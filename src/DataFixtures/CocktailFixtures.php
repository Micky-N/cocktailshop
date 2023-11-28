<?php

namespace App\DataFixtures;

use App\Entity\Cocktail;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CocktailFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $data = json_decode(file_get_contents('https://thecocktaildb.com/api/json/v1/1/search.php?s=d'), true);
        $slugger = new AsciiSlugger();

        foreach ($data['drinks'] as $drink){
            $cocktail = new Cocktail();
            $cocktail->setName($drink['strDrink']);
            $cocktail->setSlug($slugger->slug($drink['strDrink'])->lower());
            $cocktail->setUrl($drink['strDrinkThumb']);
            $cocktail->setDescription($drink['strInstructions'] ?? null);
            $cocktail->setIngredients($this->getIngredients($drink));
            $cocktail->setVip($drink['strCreativeCommonsConfirmed'] == 'Yes');
            $cocktail->setPrice(mt_rand(1000, 10000)/100);
            $manager->persist($cocktail);
        }

        $manager->flush();
    }

    private function getIngredients(array $drinkAPI): array
    {
        $ingredients = [];
        for($i = 1; $i <= 15; $i++){
            $ingredients[] = $drinkAPI['strIngredient'.$i];
        }
        return array_filter($ingredients, fn($ingredient) => $ingredient);
    }
}
