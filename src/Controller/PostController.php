<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;

class PostController extends AbstractController
{   
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/post/insert', name: 'insert_post')]
    public function insert() {
        $post = new Post(
            'My Post created',
            'Debate',
            'Description',
            'myfile.png',
            'my url page'
        );
        $user = $this->em->getRepository(User::class)->find(1);
        $post->setUser($user);

//        $post->setTitle('Post insertado')
//            ->setDescription('Hello World')
//            ->setUrl('My url')
//            ->setFile('File')
//            ->setType('Opion')
//            ->setCreationDate(new \DateTime())
//            ->setUser($user);

        $this->em->persist($post);
        $this->em->flush();

        return new JsonResponse(['success' => true ]);
    }


    #[Route('/post/update', name: 'update_post')]
    public function update() {

        $post = $this->em->getRepository(Post::class)->find(4);
        $post->setTitle('My new Title');
        $this->em->persist($post);
        $this->em->flush();

        return new JsonResponse(['success' => true ]);
    }

    #[Route('/post/remove', name: 'remove_post')]
    public function remove() {

        $post = $this->em->getRepository(Post::class)->find(4);
        $this->em->remove($post);
        $this->em->flush();

        return new JsonResponse(['success' => true ]);
    }



    #[Route('/post/{id}', name: 'app_post')]
    public function index(Post $post): Response
    {
         $custom_post = $this->em->getRepository(Post::class)->findPost($post->getId());
        dump($custom_post);
        return $this->render('post/index.html.twig', [
            'controller_name' => 'PostController',
        ]);
    }


}
