<?php

namespace Drupal\jobsity_moviedb;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Class MovieService.
 *
 * @package Drupal\jobsity_moviedb
 */
class MovieService {
  /**
   * The Guzzle HTTP Client.
   *
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * Site Settings.
   *
   * @var Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * MovieService constructor.
   *
   * @param \Drupal\Core\Http\ClientFactory $http_client_factory
   *   The client factory interface.
   * @param \Drupal\Core\Site\Settings $settings
   *   The site settings object.
   */
  public function __construct($http_client_factory, $settings) {
    $this->client = $http_client_factory->fromOptions([
      'base_uri' => 'https://api.themoviedb.org/3/',
    ]);
  }

  /**
   * Query themoviedb.org for artists and movies.
   *
   * @param string $search_category
   *   The category to search.
   * @param string $params
   *   The append_to_response fields to search for.
   * @param int $page
   *   The page to search for.
   *
   * @return array
   *   The result object.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   *    In case request fails or breaks.
   */
  public function searchTMDB($search_category = '', $params = '', $page = 1) {
    try {
      $response = $this->client->get($search_category, [
        'headers' => [
          'Accept' => 'application/json',
        ],
        'query' => [
          'api_key' => '47219d3b05ba36a7dc2498d8e7627970',
          'language' => 'en-US',
          'append_to_response' => $params,
          'page' => $page
        ],
      ]);
      $results = Json::decode($response->getBody());

      return $results;
    }
    catch (GuzzleException $e) {
      \Drupal::logger('jobsity_moviedb')->error($e->getMessage());
    }
  }

  /**
   * Create a Movie node in the DB.
   *
   * @param array $movie
   *   The movie to insert.
   *
   * @return string
   *   The movie ID.
   */
  public function createMovie($movie = []) {
    $configuration = self::searchTMDB('configuration');
  
    $data = file_get_contents($configuration['images']['base_url'] . "w500" . $movie['poster_path']);
    $data_name = ltrim($movie['poster_path'], '/');
    $file = file_save_data($data, 'public://' . $data_name, 1);
  
    // Declare empty alternative_titles array.
    $alternative_titles = [];
  
    // Fill trailer field.
    $trailer = '';
    if(isset($movie['videos']['results'])) {
      $video = reset($movie['videos']['results']);
      if ($video) {
        $trailer = $video['key'] . '|' . $video['site'];
      }
    }
  
    // Fill production companies.
    $production_companies = [];
    if(isset($movie['production_companies'])) {
      foreach($movie['production_companies'] as $company) {
        $production_companies[] = $company['name'];
      }
    }
  
    // Fill Genres.
    $genres = [];
    if(isset($movie['genres'])) {
      foreach($movie['genres'] as $genre) {
        $properties = [];
        $properties['name'] = $genre['name'];
        $properties['vid'] = 'genre';
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
        $term = reset($terms);
        $genres[] = $term->id();
      }
    }
  
    // Fill Reviews.
    $reviews = [];
    if(isset($movie['reviews']['results'])) {
      foreach($movie['reviews']['results'] as $review) {
        $reviews[] = $review['content'];
      }
    }
  
    // Create node object and save it.
    $node = Node::create([
      'type' => 'movie',
      'title' => $movie['original_title'],
      'field_remote_id' => $movie['id'],
      'field_alternative_titles' => $alternative_titles,
      'field_genre' => $genres,
      'field_original_language' => $movie['original_language'],
      'field_overview' => $movie['overview'],
      'field_popularity' => $movie['popularity'],
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Sample',
        'title' => $movie['original_title'],
      ],
      'field_production_companies' => $production_companies,
      'field_release_date' => $movie['release_date'],
      'field_trailer' => $trailer,
      'field_reviews' => $reviews
    ]);
    $node->save();

    return $node->id();
  }

  /**
   * Create an Artist node in the DB.
   *
   * @param array $artist
   *   The artist to insert.
   *
   * @return string
   *   The artist ID.
   */
  public function createArtist($artist = []) {
    $configuration = self::searchTMDB('configuration');
  
    $data = file_get_contents($configuration['images']['base_url'] . "w185" . $artist['profile_path']);
    $data_name = ltrim($artist['profile_path'], '/');
    $file = file_save_data($data, 'public://' . $data_name, 1);
  
    // Create node object and save it.
    $node = Node::create([
      'type' => 'artist',
      'title' => $artist['name'],
      'field_remote_id' => $artist['id'],
      'field_bio' => $artist['biography'],
      'field_popularity' => $artist['popularity'],
      'field_image' => [
        'target_id' => $file->id(),
        'alt' => 'Sample',
        'title' => $artist['name'],
      ],
      'field_date_of_birth' => $artist['birthday'],
      'field_date_of_death' => $artist['deathday'],
      'field_place_of_birth' => $artist['place_of_birth'],
      'field_website' => $artist['homepage'],
    ]);
    $node->save();

    return $node->id();
  }

}
