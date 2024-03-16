<?php

// src/Service/ConfigurationService.php

namespace App\Service;

use App\Repository\ConfigurationRepository;

class DataConfigurationService
{
    private $configurationRepository;

    public function __construct(ConfigurationRepository $configurationRepository)
    {
        $this->configurationRepository = $configurationRepository;
    }

    public function getLogo1(): ?string
    {
        // Implémentez la logique pour récupérer et retourner la valeur de logo1
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'logo1'])->getValeur() : 'men.png';
    }

    public function getLogo2(): ?string
    {
        // Implémentez la logique pour récupérer et retourner la valeur de logo2
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'logo2'])->getValeur() : 'men.png';
    }

    public function getName(): ?string
    {
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'name'])->getValeur() : 'IGEF';
    }

    public function getTel(): ?string
    {
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'tel'])->getValeur() : '33 000 00 00';
    }

    public function getEmail(): ?string
    {
        return $this->configurationRepository->findAll() ? $this->configurationRepository->findOneBy(['cle' => 'email'])->getValeur() : 'email@gmail.com';
    }

    public function getTitle1(): ?string
    {
        return $this->configurationRepository->findAll() ? $this->configurationRepository->findOneBy(['cle' => 'title_1'])->getValeur() : 'IGEF';
    }

    public function getTitle2(): ?string
    {
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'title_2'])->getValeur() : 'IGEF';
    }

    public function getUri(): ?string
    {
        return $this->configurationRepository->findAll() ?  $this->configurationRepository->findOneBy(['cle' => 'uri'])->getValeur() : 'localhost:8000';
    }
}
