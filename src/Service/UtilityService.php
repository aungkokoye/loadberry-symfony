<?php


namespace App\Service;


use App\Repository\SettingRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class UtilityService
{
    const MAX_FILE_UPLOAD_SIZE_KEY = 'file_upload_size';
    const DEFAULT_FILE_FILE_UPLOAD_SIZE = '20m';

    /**
     * @var SettingRepository
     */
    private $settingRepo;

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * utilityService constructor.
     * @param SettingRepository $settingRepo
     */
    public function __construct(SettingRepository  $settingRepo)
    {
        $this->settingRepo = $settingRepo;
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getMaxFileUploadSize()
    {
        return $this->cache->get(self::MAX_FILE_UPLOAD_SIZE_KEY, function (ItemInterface $item) {
            $item->expiresAfter(3600 * 24 * 7); //one week
            $setting = $this->settingRepo->findOneByKeyName(self::MAX_FILE_UPLOAD_SIZE_KEY);
            $value = $setting->getValue();
            if(empty($value)) {
                return self::DEFAULT_FILE_FILE_UPLOAD_SIZE;
            }
            return $value;
        });
    }
}
