<?php

namespace Services\Back;

use Entity\Video;
use Entity\Serie;

class SerieManagerService
{
    /**
     * this function create a serie
     *
     * @param integer $video
     * @param integer $episode
     * @param integer $season
     * @param string $image
     * @return void
     */
    public function createSerie(int $video, int $episode, int $season, string $image): void
    {
        try {
            $serie = Serie::insertOne([
                "video_id" => Video::findFirst(["id" => $video])->getid(),
                "episode" => $episode,
                "season" => $season,
                "image" => $image
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error creating serie - " . $e->getMessage());
        }
    }

    /**
     * this function delete a serie
     *
     * @param integer $id
     * @return void
     */
    public function deleteSerie(int $id): void
    {
        try {
            $serie = Serie::findFirst(["id" => $id]);
            $serie->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error deleting serie - " . $e->getMessage());
        }
    }

    /**
     * this function update a serie (episode, season, image)
     *
     * @param integer $id
     * @param integer $episode
     * @param integer $season
     * @param string $image
     * @return void
     */
    public function updateSerie(int $id, int $episode, int $season, string $image): void
    {
        try {
            $serie = Serie::findFirst(["id" => $id]);
            $serie->setEpisode($episode);
            $serie->setSeason($season);
            $serie->setImage($image);
            $serie->save();
        } catch (\Exception $e) {
            throw new \Exception("Error updating serie - " . $e->getMessage());
        }
    }
}
