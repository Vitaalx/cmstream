<?php

namespace Services\Back;

use Entity\Category;

class CategoryManagerService
{
    /**
     * this function create a category
     *
     * @param string $title
     * @return void
     */
    public function createCategory(string $title): void
    {
        try {
            if (Category::findFirst(["title" => $title]) !== null) {
                throw new \Exception("Category already exists");
            }
            $category = Category::insertOne([
                "title" => $title
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Category not found");
        }
    }

    /**
     * this function delete a category
     *
     * @param integer $id
     * @return void
     */
    public function deleteCategory(int $id): void
    {
        try {
            $category = Category::findFirst([
                "id" => $id
            ]);
            $category->delete();
        } catch (\Exception $e) {
            throw new \Exception("Category not found");
        }
    }

    /**
     * this function update a category
     *
     * @param integer $id
     * @param string $title
     * @return void
     */
    public function updateCategory(int $id, string $title): void
    {
        try {
            $category = Category::findFirst([
                "id" => $id
            ]);
            $category->setTitle($title);
            $category->save();
        } catch (\Exception $e) {
            throw new \Exception("Category not found");
        }
    }

    /**
     * this function get a category
     *
     * @param integer $id
     * @return Category
     */
    public function getCategory(int $id): Category
    {
        try {
            $category = Category::findFirst([
                "id" => $id
            ]);
            return $category;
        } catch (\Exception $e) {
            throw new \Exception("Category not found");
        }
    }

    /**
     * this function get all categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        try {
            $categories = Category::findMany();
            return $categories;
        } catch (\Exception $e) {
            throw new \Exception("Categories not found");
        }
    }
}
