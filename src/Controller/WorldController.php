<?php

namespace App\Controller;

use App\Entity\GeoData;
use App\Form\EditGeoDataType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WorldController extends AbstractController {

	#[Route('/world/regions', name:'maf_world_regions')]
	#[Route('/world/regions/{page}', name:'maf_world_regions_page')]
	public function regionsAction(EntityManagerInterface $em, $page = 1) {
		$count = ($page - 1) * 100;

		$query = $em->createQuery('SELECT g FROM App:GeoData g WHERE g.id > :count ORDER BY g.id ASC');
		$query->setParameters(['count'=>$count]);

		return $this->render('World/regions.html.twig', [
			'regions' => $query->getResult(),
			'page' => $page
		]);
	}

	#[Route('/world/region/{region}', name:'maf_world_region_edit', requirements: ['region'=>'\d+'])]
	public function regionEditAction(Request $request, GeoData $region) {
		$form = $this->createForm(new EditGeoDataType(), $region);
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			# TODO: Need to add logic for handling resources here.
			#$this->getDoctrine()->getManager()->flush();
			return new RedirectResponse($this->generateUrl('maf_world_regions').'#'.$region->getId());
		}

		return $this->render('World/editRegion.html.twig', [
			'region'=>$region,
			'form'=>$form->createView(),
		]);
	}

}
