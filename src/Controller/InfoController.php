<?php

namespace App\Controller;

use App\Entity\BuildingType;
use App\Entity\EntourageType;
use App\Entity\FeatureType;
use App\Entity\EquipmentType;
use App\Service\PageReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class InfoController extends AbstractController {
	
	private EntityManagerInterface $em;
	private PageReader $pager;
	
	public function __construct(EntityManagerInterface $em, PageReader $pager) {
		$this->em = $em;
		$this->pager = $pager;
	}

	#[Route ('/info/buildings', name:'maf_info_buildings')]
	public function allbuildingtypesAction(Request $request): Response {

		return $this->render('Info/all.html.twig', $this->alltypes('BuildingType', $request));
	}

	#[Route ('/info/building/{id}', name:'maf_info_building', requirements:['id'=>'\d+'])]
	public function buildingtypeAction($id): Response {
		$em = $this->em;
		$buildingtype = $em->getRepository(BuildingType::class)->find($id);
		if (!$buildingtype) {
			throw $this->createNotFoundException('error.notfound.buildingtype');
		}

		return $this->render('Info/buildingtype.html.twig', [
			"buildingtype" => $buildingtype
		]);
	}

	#[Route ('/info/features', name:'maf_info_features')]
	public function allfeaturetypesAction(Request $request): Response {

		return $this->render('Info/all.html.twig', $this->alltypes('FeatureType', $request));
	}

	#[Route ('/info/feature/{id}', name:'maf_info_featuretype', requirements:['id'=>'\d+'])]
	public function featuretypeAction($id): Response {
		$em = $this->em;
		$featuretype = $em->getRepository(FeatureType::class)->find($id);
		if (!$featuretype) {
			throw $this->createNotFoundException('error.notfound.featuretype');
		}

		return $this->render('Info/featuretype.html.twig', [
			"featuretype" => $featuretype
		]);
	}

	#[Route ('/info/entourages', name:'maf_info_entourages')]
	public function allentouragetypesAction(Request $request): Response {

		return $this->render('Info/all.html.twig', $this->alltypes('EntourageType', $request));
	}

	#[Route ('/info/entourage/{id}', name:'maf_info_entourage', requirements:['id'=>'\d+'])]
	public function entouragetypeAction($id): Response {
		$em = $this->em;
		$entouragetype = $em->getRepository(EntourageType::class)->find($id);
		if (!$entouragetype) {
			throw $this->createNotFoundException('error.notfound.entouragetype');
		}

		return $this->render('Info/entouragetype.html.twig', [
			"entouragetype" => $entouragetype
		]);
	}

	#[Route ('/info/equipments', name:'maf_info_equipments')]
	public function allequipmenttypesAction(Request $request): Response {

		return $this->render('Info/all.html.twig', $this->alltypes('EquipmentType', $request));
	}

	#[Route ('/info/equipment/{id}', name:'maf_info_equipment', requirements:['id'=>'\d+'])]
	public function equipmenttypeAction($id): Response {
		$em = $this->em;
		$equipmenttype = $em->getRepository(EquipmentType::class)->find($id);
		if (!$equipmenttype) {
			throw $this->createNotFoundException('error.notfound.equipmenttype');
		}

		return $this->render('Info/equipmenttype.html.twig', [
			"equipmenttype" => $equipmenttype
		]);
	}


	private function alltypes($type, $request): array {
		$em = $this->em;
		$all = $em->getRepository($type::class)->findBy([], ['name'=>'asc']);
		$toc = $this->pager->getPage('manual', 'toc', $request->getLocale());

		return [
			"toc" => $toc,
			"list" => strtolower($type).'s',
			"all" => $all
		];
	}

	#[Route ('/info/{page}', name:'maf_info_manualhack')]
	public function infoWildcardAction($page): RedirectResponse {
		return $this->redirectToRoute('maf_manual', ['page'=>$page]);
	}
}
