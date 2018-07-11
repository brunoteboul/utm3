<?php
namespace Utm\Model;

class App extends \Utm\CoreModel
{
    protected $product = [];
    
    public function __construct()
    {
        $this->product = [
            1 => ['id' => 1, 'titre' => "Apple Watch Serie 2", 'img' => 'img/produit-applewatch.jpg'],
            2 => ['id' => 2, 'titre' => "Macbook air 11 pouces", 'img' => 'img/produit-macbookair.jpg'],
            3 => ['id' => 3, 'titre' => "Drone Parrot Bebop + Skycontroller", 'img' => 'img/produit-bebop.jpg'],
            4 => ['id' => 4, 'titre' => "Samsung Galaxy S7 edge", 'img' => 'img/produit-galaxys7edge.jpg'],
            5 => ['id' => 5, 'titre' => "Gopro Hero 5 Black + Karma Grip", 'img' => 'img/produit-hero5gopro.jpg'],
            6 => ['id' => 6, 'titre' => "Iphone 7 plus", 'img' => 'img/produit-iphone.jpg'],
            7 => ['id' => 7, 'titre' => "Ipad pro 12,9 pouces", 'img' => 'img/produit-ipadpro.jpg'],
            8 => ['id' => 8, 'titre' => "Surface pro 4", 'img' => 'img/produit-surface.jpg'],
            9 => ['id' => 9, 'titre' => "ASUS Zenbook Flip UX360CA-C4004R", 'img' => 'img/produit-zenbook.jpg'],
        ];
    }
    
    public function getProductList()
    {
        return $this->product;
    }

}
