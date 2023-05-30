<?php

namespace controller\API\ContentManager\CategoryController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Category;
use Services\Back\CategoryManagerService as CategoryManager;

/**
 * @api {post} /api/content-manager/category/create
 * @apiName CreateCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a category
 * @param string name
 * @return Response
 */
/*
 Entry:
 {
  "name": "Category name"
 }
*/
class createCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["category/name", $request->getBody()['name']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $categoryManager = new CategoryManager();

            $categoryManager->createCategory(
                $this->floor->pickup("category/name")
            );

            $response->send(["message" => "Category created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {delete} /api/content-manager/category/delete
 * @apiName DeleteCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete a category
 * @param string name
 * @return Response
 */
/*
Entry:
{
"name": "Category name"
}
*/
class deleteCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["category/name", $request->getBody()['name']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $categoryManager = new CategoryManager();

            $categoryManager->deleteCategory(
                $this->floor->pickup("category/name")
            );

            $response->send(["message" => "Category deleted"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {get} /api/content-manager/category/get-all
 * @apiName GetAllCategories
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all categories
 * @return Response
 */
class getAllCategories extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $categoryManager = new CategoryManager();

            $categories = $categoryManager->getCategories();

            $response->send(["categories" => $categories]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {get} /api/content-manager/category/get-all-content
 * @apiName GetAllContentWhereCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all content where category
 * @param string name
 * @return Response
 */
/*
Entry:
{
"name": "Category name"
}
*/
class getAllContentWhereCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["category/name", $request->getQuery('name')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $category = Category::findFirst(["title" => $this->floor->pickup("category/name")]);
            $videos = $category->getVideos();
            $content = [];
            foreach ($videos as $video) {
                if ($video->getFilm() != null) {
                    $film = $video->getFilm();
                    $content[] = [
                        "id_film" => $film->getId(),
                        "title" => $film->getVideo()->getTitle(),
                        "description" => $film->getVideo()->getDescription(),
                        "created_at" => $film->getCreatedAt(),
                        "updated_at" => $film->getUpdatedAt()
                    ];
                } else if ($video->getSeries() != null) {
                    $episodes = $video->getSeries();
                    foreach ($episodes as $episode) {
                        if (!in_array($episode->getTitle(), array_column($content, "title"))) {
                            $content[] = [
                                "id_serie" => $episode->getId(),
                                "title" => $episode->getTitle(),
                                "image" => $episode->getImage(),
                                "created_at" => $episode->getCreatedAt(),
                                "updated_at" => $episode->getUpdatedAt()
                            ];
                        }
                    }
                } else {
                    throw new \Exception("Error getting content");
                }
            }

            $response->send(["content" => $content]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}
