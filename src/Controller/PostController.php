<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PostType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{   
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }
    #[Route('/', name: 'app_post')]
    public function index(
        Request $request,
        SluggerInterface $slugger,

        PaginatorInterface $paginator
    ): Response {
        $post = new Post();
        $query = $this->em->getRepository(Post::class)->findPostByPagination();

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            2
        );

//        $posts = $this->em->getRepository(Post::class)->findAllPost();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            $url =  str_replace(' ', '-', $form->get('title')->getData());

            if ($file) {
                $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $newFileName = $safeFileName.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFileName
                    );
                } catch(FileException $e) {
                    throw new \Exception('Ups there is  a problem with your file');
                }

                $post->setFile($newFileName);
            }

            $user = $this->em->getRepository(User::class)->find(1);
            $post->setUser($user);
            $post->setUrl($url);
            $this->em->persist($post);

            $this->em->flush();

            return $this->redirectToRoute('app_post');
        }

        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $pagination
        ]);
    }

    #[Route('/post/details/{id}', name: 'post_detail')]
    public function postDetail(Post $post) {
        return $this->render('post/post-detail.html.twig', ['post' => $post]);
    }



}
