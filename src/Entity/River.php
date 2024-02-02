<?php

namespace App\Entity;

use LongitudeOne\Spatial\PHP\Types\Geometry\LineString;

class River {
	private string $name;
	private linestring $course;
	private int $id;

	/**
	 * Set name
	 *
	 * @param string $name
	 *
	 * @return River
	 */
	public function setName(string $name): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Get name
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Set course
	 *
	 * @param linestring $course
	 *
	 * @return River
	 */
	public function setCourse(LineString $course): static {
		$this->course = $course;

		return $this;
	}

	/**
	 * Get course
	 *
	 * @return linestring
	 */
	public function getCourse(): LineString {
		return $this->course;
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId(): int {
		return $this->id;
	}
}
