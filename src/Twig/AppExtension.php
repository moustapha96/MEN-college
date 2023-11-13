<?php


// src/Twig/AppExtension.php

namespace App\Twig;

use App\Service\ConfigurationService;
use App\Service\DataConfigurationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $configurationService;

    public function __construct(DataConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getLogo1', [$this, 'getLogo1']),
            new TwigFunction('getLogo2', [$this, 'getLogo2']),
            new TwigFunction('getName', [$this, 'getName']),
            new TwigFunction('getTel', [$this, 'getTel']),
            new TwigFunction('getEmail', [$this, 'getEmail']),
            new TwigFunction('getTitle1', [$this, 'getTitle1']),
            new TwigFunction('getTitle2', [$this, 'getTitle2']),
        ];
    }

    public function getLogo1(): ?string
    {
        return $this->configurationService->getLogo1();
    }

    public function getLogo2(): ?string
    {
        return $this->configurationService->getLogo2();
    }

    public function getName(): ?string
    {
        return $this->configurationService->getName();
    }



    public function getTel(): ?string
    {
        return $this->configurationService->getTel();
    }

    public function getEmail(): ?string
    {
        return $this->configurationService->getEmail();
    }

    public function getTitle1(): ?string
    {
        return $this->configurationService->getTitle1();
    }

    public function getTitle2(): ?string
    {
        return $this->configurationService->getTitle2();
    }
}
