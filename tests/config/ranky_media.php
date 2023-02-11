<?php
declare(strict_types=1);


use Ranky\MediaBundle\Tests\Dummy\User\Domain\User;
use Symfony\Config\RankyMediaConfig;

return static function (RankyMediaConfig $rankyMediaConfig): void {

    $rankyMediaConfig
        ->userEntity(User::class)
        ->uploadUrl('%env(SITE_URL)%/uploads')
        ->userIdentifierProperty('username')
        ->paginationLimit(30)
    ;
};
