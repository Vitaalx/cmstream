<?php

namespace controller\API\ContentManager\CategoryController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Category;

/**
 * @api {post} /api/content-manager/category/create
 * @apiName CreateCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a category
 * @param string categorie_name
 * @return Response
 */
/*
 Entry:
 {
  "categorie_name": "Category name"
 }
*/
class createCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['categorie_name'], "category_name"],
            ["category/name", fn () => $this->floor->pickup("category_name"), "category_name"],
            ["category/notexist", fn () => $this->floor->pickup("category_name")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            Category::insertOne(["title" => $this->floor->pickup("category_name")]);

            $response->info("category.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("category.error")->code(500)->send();
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
 * @param int category_id
 * @return Response
 */
/*
Entry:
{
"category_id": 1
}
*/
class deleteCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $category = $this->floor->pickup("category");
        $category->delete();
        $response->info("category.deleted")->code(200)->send();
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
            $categories = Category::findMany();
            $response->send(["categories" => $categories]);
        } catch (\Exception $e) {
            $response->info("category.error")->code(500)->send();
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
 * @param int category_id
 * @return Response
 */
/*
Entry:
{
"category_id": 1
}
*/
class getAllContentWhereCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videos = $this->floor->pickup("category")->getVideos();
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
                    throw new \Exception("Video not found");
                }
            }

            $response->send(["content" => $content]);
        } catch (\Exception $e) {
            $response->info("category.error")->code(500)->send();
        }
    }
}
/**
 * @api {put} /api/content-manager/category/update
 * @apiName UpdateCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update a category
 * @param int category_id
 * @param string category_name
 * @return Response
 */
/*
Entry:
{
"category_id": 1,
"category_name": "Category name"
}
*/
class updateCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
            ["type/string", $request->getBody()['category_name'], "category_name"],
            ["category/name", fn () => $this->floor->pickup("category_name"), "category_name"],
            ["category/notexist", fn () => $this->floor->pickup("category_name")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $category = $this->floor->pickup("category");
            $category->setTitle($this->floor->pickup("category_name"));
            $category->setUpdatedAt(date("Y-m-d H:i:s"));
            $category->save();

            $response->info("category.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("category.error")->code(500)->send();
        }
    }
}
