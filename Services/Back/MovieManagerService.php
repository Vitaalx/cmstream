<?php

namespace Services\Back;

use Entity\Video;
use Entity\Film;

class MovieManagerService
{
    /**
     * this function create a movie where video
     *
     * @param integer $video
     * @param string $image
     * @return void
     */
    public function createMovie(int $video, string $image): void
    {
        try {
            $film = Film::insertOne([
                "video" => Video::findFirst(["id" => $video]),
                "image" => $image
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error creating movie - " . $e->getMessage());
        }
    }

    /**
     * this function delete a movie
     *
     * @param integer $id
     * @return void
     */
    public function deleteMovie(int $id): void
    {
        try {
            $film = Film::findFirst(["id" => $id]);
            $film->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error deleting movie - " . $e->getMessage());
        }
    }

    /**
     * this function update a movie (image)
     *
     * @param integer $id
     * @param string $image
     * @return void
     */
    public function updateMovie(int $id, string $image): void
    {
        try {
            $film = Film::findFirst(["id" => $id]);
            $film->setImage($image);
            $film->save();
        } catch (\Exception $e) {
            throw new \Exception("Error updating movie - " . $e->getMessage());
        }
    }
}
