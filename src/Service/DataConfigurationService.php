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
        return $this->configurationRepository->findOneBy(['cle' => 'logo1'])->getValeur();
    }

    public function getLogo2(): ?string
    {
        // Implémentez la logique pour récupérer et retourner la valeur de logo2
        return $this->configurationRepository->findOneBy(['cle' => 'logo2'])->getValeur();
    }

    public function getName(): ?string
    {
        return $this->configurationRepository->findOneBy(['cle' => 'name'])->getValeur();
    }

    public function getTel(): ?string
    {
        return $this->configurationRepository->findOneBy(['cle' => 'tel'])->getValeur();
    }

    public function getEmail(): ?string
    {
        return $this->configurationRepository->findOneBy(['cle' => 'email'])->getValeur();
    }

    public function getTitle1(): ?string
    {
        return $this->configurationRepository->findOneBy(['cle' => 'title_1'])->getValeur();
    }

    public function getTitle2(): ?string
    {
        return $this->configurationRepository->findOneBy(['cle' => 'title_2'])->getValeur();
    }
}
