<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryAdminType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class AdminCategoryController extends AbstractController
{
    private $managerRegistry;
    
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    
    #[Route('/admin/categories', name: 'admin_category')]
    public function index(CategoryRepository $categoryRepository, Request $request): Response
    {
        $category = new Category();
        
        $form = $this->createForm(CategoryAdminType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->add($category, true);
        }
        
        $currentPage = $request->query->get('page', 1);

        $categories = $categoryRepository->findParentCategories($currentPage);
        
        return $this->render('admin/category/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
            'currentPage' => $currentPage
        ]);
    }
    
    #[Route('/admin/categories/create', name: 'admin_category_create')]
    public function create(Request $request, Category $parentCategory = null): Response
    {
        $category = new Category();
        $category->setParent($parentCategory);
        
        $form = $this->createForm(CategoryAdminType::class, $category, [
            'parent' => $parentCategory ? $parentCategory->getParent() == null : false
        ]);
        $form->handleRequest($request);
        
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager = $this->managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();
            
            return $this->redirectToRoute('admin_category_show', [
                'id' => $category->getId()
            ]);
        }
        
        return $this->render('admin/category/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/admin/categories/{id}/edit', name: 'admin_category_edit')]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryAdminType::class, $category, [
            'parent' => $category->getParent() != null
        ]);
        $form->handleRequest($request);
        
        // Check if form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->managerRegistry->getManager()->flush();
            
            $this->addFlash('success', 'Informations saved');
        }
        
        return $this->render('admin/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }
    
    #[Route('/admin/categories/{id}', name: 'admin_category_show')]
    public function show(Request $request, Category $category): Response
    {
        $newCategory = new Category();
        $newCategory->setParent($category);
        
        $form = $this->createForm(CategoryAdminType::class, $newCategory);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->managerRegistry->getManager()->persist($newCategory);
            $this->managerRegistry->getManager()->flush();
        }
        
        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/admin/categories/{id}/delete', name: 'admin_category_delete')]
    public function delete(CategoryRepository $categoryRepository, Category $category): Response
    {
        if($category->getParent() == null) {
            foreach($categoryRepository->findBy(['parent' => $category]) as $subcategory) {
                $this->managerRegistry->getManager()->remove($subcategory);
            }
        }
        
        $this->managerRegistry->getManager()->remove($category);
        $this->managerRegistry->getManager()->flush();
        
        if($category->getParent()) {
            return $this->redirectToRoute('admin_category_show', [
                'id' => $category->getParent()->getId()
            ]);
        } else {
            return $this->redirectToRoute('admin_category');
        }
    }
}
