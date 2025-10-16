<?php
require_once __DIR__ . '/../Models/ExploreModel.php';

class ExploreController {
    private $model;

    public function __construct() {
        $this->model = new ExploreModel();
    }

    public function index() {
        $posts = $this->model->getExplorePosts();
        require __DIR__ . '/../Views/Explore.php';
    }
}