<?php

namespace App\Controller;

use App\Form\TaskForm;
use App\Form\ListForm;
use App\Entity\Liste;
use App\Entity\Tache;
use App\Repository\ListeRepository;
use App\Repository\TacheRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

class ToDoController extends AbstractController
{
    /**
     * @Route("/", name="homePage")
     */
    public function home(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $lists = $entityManager->getRepository(Liste::class)->findAll();
        
        return $this->render('toDoList/main.html.twig', [
            'lists' => $lists,
        ]);
    }

    /**
     * @Route("/add/{name}", name="add")
     */
    public function add(
        ManagerRegistry $doctrine,
        EntityManagerInterface $manager,
        Request $request,
        string $name

    ): Response
    {
        $task = new Tache();
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $list = $entityManager->getRepository(Liste::class)->findOneBy(['nom' => $name]);
            $title = $form['titre']->getData();

            $task->setTitre($title);
            $task->setFait(false);
            $task->setListe($list);

            $manager->persist($task);
            $manager->flush();

            return $this->redirectToRoute('toDoList', array('name' => $name));
        }

        return $this->renderForm('toDoList/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(
        ManagerRegistry $doctrine,
        EntityManagerInterface $manager,
        Request $request
    ): Response
    {
        $list = new Liste();
        $form = $this->createForm(ListForm::class, $list);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form['nom']->getData();

            $entityManager = $doctrine->getManager();
            $oldList = $entityManager->getRepository(Liste::class)->findOneBy(['nom' => $name]);

            if (!$oldList) {
                $list->setNom($name);
                $manager->persist($list);
                $manager->flush();
            }

            return $this->redirectToRoute('toDoList', array('name' => $name));
        }

        return $this->renderForm('toDoList/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(ManagerRegistry $doctrine, Liste $list): Response
    {
        $entityManager = $doctrine->getManager();
        $tasks = $list->getTaches();
        foreach ($tasks as $task) {
            $entityManager->remove($task);
        }
        $entityManager->remove($list);
        $entityManager->flush();
        return $this->redirectToRoute('homePage');
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(
        EntityManagerInterface $manager,
        Request $request,
        Liste $list
    ): Response
    {
        $form = $this->createForm(ListForm::class, $list);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form['nom']->getData();
            $list->setNom($name);
            $manager->persist($list);
            $manager->flush();
            return $this->redirectToRoute('toDoList', array('name' => $name));
        }

        return $this->renderForm('toDoList/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/editTask/{id}", name="editTask")
     */
    public function editTask(
        EntityManagerInterface $manager,
        Request $request,
        Tache $task
    ): Response
    {
        $name = $task->getListe()->getNom();
        $form = $this->createForm(TaskForm::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $title = $form['titre']->getData();
            $task->setTitre($title);
            $manager->persist($task);
            $manager->flush();
            return $this->redirectToRoute('toDoList', array('name' => $name));
        }

        return $this->renderForm('toDoList/add.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @Route("/deleteTask/{id}", name="deleteTask")
     */
    public function deleteTask(ManagerRegistry $doctrine, Tache $task): Response
    {
        $name = $task->getListe()->getNom();
        $entityManager = $doctrine->getManager();
        $entityManager->remove($task);
        $entityManager->flush();
        return $this->redirectToRoute('toDoList', array('name' => $name));
    }

    /**
     * @Route("/updateState/{id}", name="updateState")
     */
    public function updateState(ManagerRegistry $doctrine, Tache $task): Response
    {
        $entityManager = $doctrine->getManager();
        if(!$task->isFait()) {
            $task->setFait(true);
        } else {
            $task->setFait(false);
        }
        $entityManager->flush();
        $list = $task->getListe()->getNom();
        return $this->redirectToRoute('toDoList', array('name' => $list));
    }

    /**
     * @Route("/toDoList/{name}", name="toDoList")
     */
    public function toDoList(ManagerRegistry $doctrine, string $name): Response
    {

        $entityManager = $doctrine->getManager();
        $list = $entityManager->getRepository(Liste::class)->findOneBy(['nom' => $name]);
        $tasks = $list->getTaches();
        $id = $list->getId();

        return $this->render('toDoList/list.html.twig', [
            'id' => $id,
            'name' => $name,
            'tasks' => $tasks,
        ]);
    }

}