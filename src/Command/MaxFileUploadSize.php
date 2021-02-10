<?php

namespace App\Command;

use App\Entity\Setting;
use App\Service\UtilityService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class MaxFileUploadSize extends Command
{
    protected static $defaultName = 'app:max-file-upload-size';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();

    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Update max file upload size.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows to update max file upload size. param1 : file_size (int) .')
            ->addArgument('file_size', InputArgument::REQUIRED, 'Max file upload size.');
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileSize = $input->getArgument('file_size');

        $setting = $this->entityManager->getRepository('App:Setting')
            ->findOneByKeyName(UtilityService::MAX_FILE_UPLOAD_SIZE_KEY);

        if (!$setting instanceof Setting) {
            $output->writeln([
                                 'Cannot continue the request. 404 error!',
                             ]);
            return 0;
        }

        switch (true) {
            case $fileSize > 1000:
                $value = 100;
                break;
            case $fileSize >= 50:
                $value = 50;
                break;
            case $fileSize >= 40;
                $value = 40;
                break;
            case $fileSize >= 30;
                $value = 30;
                break;
            default:
                $value = 20;
        }

        $setting->setValue($value . 'm');
        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        $cache = new FilesystemAdapter();
        $cacheValue = $cache->getItem(UtilityService::MAX_FILE_UPLOAD_SIZE_KEY);
        $cacheValue->set($value . 'M');
        $cache->save($cacheValue);

        $output->writeln([
                             'Successfully updated file upload size as ' . $value . 'M!',
                         ]);
        return 0;
    }

}