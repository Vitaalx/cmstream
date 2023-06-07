<?php

namespace Controller\API\ContentManager\CategoryController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Category;
use Services\MustBeAdmin;

/**
 * @POST{/api/category}
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
class createCategory extends MustBeAdmin
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
 * @DELETE{/api/category/{id}}
 * @apiName DeleteCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete a category
 * @param int id
 * @return Response
 */
class deleteCategory extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "category_id"],
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
 * @GET{/api/categories}
 * @apiName GetCategories
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get categories
 * @return Response
 */
class getCategories extends Controller
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
 * @GET{/api/contents/category/{id}}
 * @apiName GetAllContentWhereCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get contents by category
 * @param int id
 * @return Response
 */
class getAllContentWhereCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "category_id"],
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
 * @PUT{/api/category/{id}}
 * @apiName UpdateCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update a category
 * @param int id
 * @param string category_name
 * @return Response
 */
/*
Entry:
{
"category_name": "Category name"
}
*/
class updateCategory extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "category_id"],
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
