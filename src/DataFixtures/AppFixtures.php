<?php

namespace App\DataFixtures;

use App\Entity\Link;
use App\Entity\User;
use App\Services\RandomStringsService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Заполняет БД тестовыми данными.
 *
 * @package App\DataFixtures
 */
class AppFixtures extends Fixture
{
    const ALLOWED_CATEGORIES = ['video', 'car', 'news', 'audio', 'chat', 'education', 'government', 'game'];

    /**
     * @var UserPasswordEncoderInterface Энкодер паролей.
     */
    private $encoder;

    /**
     * @var RandomStringsService Сервис генерации случайных строк.
     */
    private $randomStringsService;

    /**
     * Конструктор класса.
     *
     * @param UserPasswordEncoderInterface $encoder Энкодер паролей.
     */
    public function __construct(UserPasswordEncoderInterface $encoder, RandomStringsService $randomStringsService)
    {
        $this->encoder = $encoder;
        $this->randomStringsService = $randomStringsService;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $user = new User($faker->userName);
            $user->setPassword($this->encoder->encodePassword($user, '1'));
            $manager->persist($user);
            $countLinks = mt_rand(1, 30);
            $manager->flush();

            for ($j = 0; $j < $countLinks; $j++) {
                $link = new Link();
                $linkCode = $this->randomStringsService->make();
                $link->setUser($user)
                    ->setUrl($faker->url)
                    ->setCategory(self::ALLOWED_CATEGORIES[mt_rand(0, 7)])
                    ->setUpdatedAt(new DateTime())
                    ->setCode($linkCode)
                    ->setShortUrl("http://127.0.0.1:8000/$linkCode");
                $manager->persist($link);
            }
        }

        $manager->flush();
    }
}
