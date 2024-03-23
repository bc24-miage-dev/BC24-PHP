<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'recipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResourceName $recipeTitle = null;

    #[ORM\ManyToOne(inversedBy: 'recipesThisNameIsIngredient')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ResourceName $ingredient = null;

    #[ORM\Column]
    private ?int $ingredientNumber = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipeTitle(): ?ResourceName
    {
        return $this->recipeTitle;
    }

    public function setRecipeTitle(?ResourceName $recipeTitle): static
    {
        $this->recipeTitle = $recipeTitle;

        return $this;
    }

    public function getIngredient(): ?ResourceName
    {
        return $this->ingredient;
    }

    public function setIngredient(?ResourceName $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getIngredientNumber(): ?int
    {
        return $this->ingredientNumber;
    }

    public function setIngredientNumber(int $ingredientNumber): static
    {
        $this->ingredientNumber = $ingredientNumber;

        return $this;
    }
}
