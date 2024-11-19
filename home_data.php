<?php
$api_key = 'ff3be76dc0cdcf90e42c31f9fcdd2cd8'; // Replace with your actual TMDB API key

function fetchContent($api_key, $page = 1) {
    $movies_url = "https://api.themoviedb.org/3/trending/movie/week?api_key={$api_key}&page={$page}";
    $tv_url = "https://api.themoviedb.org/3/trending/tv/week?api_key={$api_key}&page={$page}";
    
    $movies_json = file_get_contents($movies_url);
    $tv_json = file_get_contents($tv_url);
    
    $movies_data = json_decode($movies_json, true);
    $tv_data = json_decode($tv_json, true);
    
    $combined = array_merge(
        array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'description' => $item['overview'],
                'image' => 'https://image.tmdb.org/t/p/w500' . $item['poster_path'],
                'type' => 'Movie'
            ];
        }, array_slice($movies_data['results'], 0, 3)),
        array_map(function($item) {
            return [
                'id' => $item['id'],
                'title' => $item['name'],
                'description' => $item['overview'],
                'image' => 'https://image.tmdb.org/t/p/w500' . $item['poster_path'],
                'type' => 'TV Series'
            ];
        }, array_slice($tv_data['results'], 0, 3))
    );
    
    shuffle($combined);
    return array_slice($combined, 0, 3);
}

if (isset($_GET['page']) && isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(fetchContent($api_key, $_GET['page']));
    exit;
}