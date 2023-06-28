<?php

namespace Controller\API\ContentManager\CategoryController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Core\SendResponse;
use Entity\Category;
use Entity\Video;
use Services\MustBeAdmin;

/**
 * @POST{/api/category}
 * @apiName AddCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Add a category
 * @param string category_name
 * @return Response
 */
/*
 Entry:
 {
  "category_name": "Category name"
 }
*/
class addCategory extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['category_name'], "category_name"],
            ["category/name", fn () => $this->floor->pickup("category_name"), "category_name"],
            ["category/notexist", fn () => $this->floor->pickup("category_name")]
        ];
    }

    /**
     * @throws SendResponse
     */
    public function handler(Request $request, Response $response): void
    {
        /** @var Category $category */
        $category = Category::insertOne(["title" => $this->floor->pickup("category_name")]);
        $response->info("category.created")->code(201)->send($category);
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

    /**
     * @throws SendResponse
     */
    public function handler(Request $request, Response $response): void
    {
        /** @var Category $category */
        $category = $this->floor->pickup("category");
        $category->delete();
        $response->info("category.deleted")->code(204)->send();
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
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page"],
            ["type/string", $request->getQuery("name") ?? "", "name"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $page = $this->floor->pickup("page");
        $name = $this->floor->pickup("name");
        $number = 5;

        /** @var Category[] $categories */
        $categories = Category::findMany(
            [
                "title" => [
                    "\$CTN" => $name
                ]
            ],
            ["ORDER_BY" => ["id"], "OFFSET" => $number * $page, "LIMIT" => $number]
        );
        $response->code(200)->info("categories.get")->send($categories);
    }
}

/**
 * @GET{/api/contents/category/{id}}
 * @apiName getContentsByCategory
 * @apiGroup ContentManager/CategoryController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get contents by category
 * @param int id
 * @return Response
 */
class getContentsByCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
        ];
    }

    /**
     * @throws SendResponse
     */
    public function handler(Request $request, Response $response): void
    {
        /** @var Video $videos */
        $videos = $this->floor->pickup("category")->getVideos();
        $contents = [];
        foreach ($videos as $video) {
            if ($video->getMovie() != null) {
                $movie = $video->getMovie();
                $contents[] = [
                    "id_movie" => $movie->getId(),
                    "title" => $movie->getVideo()->getTitle(),
                    "description" => $movie->getVideo()->getDescription(),
                    "created_at" => $movie->getCreatedAt(),
                    "updated_at" => $movie->getUpdatedAt()
                ];
            } else if ($video->getSeries() != null) {
                $episodes = $video->getSeries();
                foreach ($episodes as $episode) {
                    if (!in_array($episode->getTitle(), array_column($contents, "title"))) {
                        $contents[] = [
                            "id_serie" => $episode->getId(),
                            "title" => $episode->getTitle(),
                            "image" => $episode->getImage(),
                            "created_at" => $episode->getCreatedAt(),
                            "updated_at" => $episode->getUpdatedAt()
                        ];
                    }
                }
            } else {
                $response->code(404)->info("video.notfound")->send();
            }
        }
        $response->code(200)->info("contents.get")->send(["contents" => $contents]);
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

    /**
     * @throws SendResponse
     */
    public function handler(Request $request, Response $response): void
    {
        /** @var Category $category */
        $category = $this->floor->pickup("category");
        $category->setTitle($this->floor->pickup("category_name"));
        $category->setUpdatedAt(date("Y-m-d H:i:s"));
        $category->save();
        $response->code(200)->info("category.updated")->send();
    }
}

/**
 * @GET{/api/categories/count}
 */
class getCountCategories extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $count = Category::count();
        $response->code(200)->info("categories.count")->send($count);
    }
}
