<?php


namespace App\EventListener;

use App\Entity\Configuration;
use App\Repository\ConfigurationRepository;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseInitializer
{
    public $configurationRepository;
    public $em;
    public function __construct(
        ConfigurationRepository $configurationRepository,
        EntityManagerInterface $em
    ) {
        $this->configurationRepository = $configurationRepository;
        $this->em = $em;
    }

    public function initialize()
    {
        // Vérifie si des données existent dans la table de configuration
        $configurations = $this->configurationRepository->findAll();

        // Si aucune donnée n'existe, insère des données initiales
        if (empty($configurations)) {
            $this->insertInitialData();
        }
    }



    private function insertInitialData()
    {
        $data = [
            ['sendMail', 'MAIL'],
            ['email', 'superadmin@admin.com'],
            ['name', 'IGEF MEN'],
            ['smsToken', ''],
            ['sendSMS', 'desable'],
            ['tel', '338645883'],
            ['title_1', 'Ministère de l’Éducation nationale'],
            ['title_2', 'Inspection générale de l\'Éducation et de la Formation'],
            ['logo1', 'flag_men.png'],
            ['logo2', 'flag_senegal.png']
        ];

        foreach ($data as $item) {
            $config = new Configuration();
            $config->setCle($item[0]);
            $config->setValeur($item[1]);
            $this->em->persist($config);
        }

        $this->em->flush();
    }
}
