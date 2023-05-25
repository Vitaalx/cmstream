<?php

namespace Services\Back;

use Entity\Category;
use Entity\Video;
use Entity\Url;
use Entity\Film;
use Entity\Serie;

class VideoManagerService
{
    /**
     * this function create a video
     *
     * @param array $url
     * @param string $title
     * @param string $description
     * @param integer $category
     * @return void
     */
    public function createVideo(array $url, string $title, string $description, int $category): Video
    {
        try {
            $video = Video::insertOne([
                "title" => $title,
                "description" => $description,
                "category_id" => Category::findFirst(["id" => $category])->getId()
            ]);
            foreach ($url as $url) {
                $this->createUrl($url, $video->getId());
            }
            return $video;
        } catch (\Exception $e) {
            throw new \Exception("Error creating video - " . $e->getMessage());
        }
    }

    /**
     * this function create a url
     *
     * @param array $url
     * @param integer $videoId
     * @return void
     */
    private function createUrl(string $url, int $video): void
    {
        try {
            Url::insertOne([
                "url" => $url,
                "video_url_id" => $video
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Error creating url");
        }
    }

    /**
     * this function delete a video
     *
     * @param integer $id
     * @return void
     */
    public function deleteVideo(int $id): void
    {
        try {
            if (Serie::findFirst(["video" => $id]) !== null) {
                $serie = Serie::findFirst(["video" => $id]);
                $serie->delete();
            } else if (Film::findFirst(["video" => $id]) !== null) {
                $film = Film::findFirst(["video" => $id]);
                $film->delete();
            }
            $urls = Url::findMany(["video_url" => $id]);
            foreach ($urls as $url) {
                $url->delete();
            }
            $video = Video::findFirst([
                "id" => $id
            ]);
            $video->delete();
        } catch (\Exception $e) {
            throw new \Exception("Error delete video: " . $e->getMessage());
        }
    }

    /**
     * this function update a video
     *
     * @param integer $id
     * @param string $title
     * @param string $description
     * @param integer $category
     * @return void
     */
    public function updateVideo(int $id, string $title, string $description, int $category): void
    {
        try {
            $video = Video::findFirst([
                "id" => $id
            ]);
            $video->setTitle($title);
            $video->setDescription($description);
            $video->setCategory(Category::findFirst(["id" => $category]));
        } catch (\Exception $e) {
            throw new \Exception("Error update video: " . $e->getMessage());
        }
    }

    /**
     * this function get url where video id
     *
     * @param integer $video
     * @return array
     */
    public function getUrlWhereVideo(int $video): array
    {
        try {
            $urls = Url::findMany([
                "video_url" => $video
            ]);
            $url = [];
            foreach ($urls as $url) {
                $url[] = $url->getUrl();
            }
            return $url;
        } catch (\Exception $e) {
            throw new \Exception("Error get url where video: " . $e->getMessage());
        }
    }

    /**
     * this function update a url
     *
     * @param integer $id
     * @param string $content (url)
     * @return void
     */
    public function updateUrlWhereId(int $id, string $content): void
    {
        try {
            $url = Url::findFirst([
                "id" => $id
            ]);
            $url->setUrl($content);
            $url->save();
        } catch (\Exception $e) {
            throw new \Exception("Error update url where id: " . $e->getMessage());
        }
    }
}
