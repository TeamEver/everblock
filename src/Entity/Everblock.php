<?php

namespace Everblock\Tools\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Everblock\Tools\Repository\EverblockRepository;
use Everblock\Tools\Service\DoctrineEntityManagerFactory;
use Everblock\Tools\Service\EverblockManager;
use Everblock\Tools\Service\EverblockCache;

#[ORM\Entity(repositoryClass: EverblockRepository::class)]
#[ORM\Table(name: 'everblock')]
class Everblock
{
    private static ?EverblockManager $manager = null;

    #[ORM\Id]
    #[ORM\Column(name: 'id_everblock', type: 'integer')]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(name: 'id_hook', type: 'integer')]
    private int $hookId = 0;

    #[ORM\Column(name: 'only_home', type: 'boolean')]
    private bool $onlyHome = false;

    #[ORM\Column(name: 'only_category', type: 'boolean')]
    private bool $onlyCategory = false;

    #[ORM\Column(name: 'only_category_product', type: 'boolean')]
    private bool $onlyCategoryProduct = false;

    #[ORM\Column(name: 'only_manufacturer', type: 'boolean')]
    private bool $onlyManufacturer = false;

    #[ORM\Column(name: 'only_supplier', type: 'boolean')]
    private bool $onlySupplier = false;

    #[ORM\Column(name: 'only_cms_category', type: 'boolean')]
    private bool $onlyCmsCategory = false;

    #[ORM\Column(name: 'obfuscate_link', type: 'boolean')]
    private bool $obfuscateLink = false;

    #[ORM\Column(name: 'add_container', type: 'boolean')]
    private bool $addContainer = false;

    #[ORM\Column(name: 'lazyload', type: 'boolean')]
    private bool $lazyload = false;

    #[ORM\Column(name: 'device', type: 'integer')]
    private int $device = 0;

    #[ORM\Column(name: 'groups', type: 'json', nullable: true)]
    private array $groups = [];

    #[ORM\Column(name: 'background', type: 'string', length: 255, nullable: true)]
    private ?string $background = null;

    #[ORM\Column(name: 'css_class', type: 'string', length: 255, nullable: true)]
    private ?string $cssClass = null;

    #[ORM\Column(name: 'data_attribute', type: 'string', length: 255, nullable: true)]
    private ?string $dataAttribute = null;

    #[ORM\Column(name: 'bootstrap_class', type: 'string', length: 255, nullable: true)]
    private ?string $bootstrapClass = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    private int $position = 0;

    #[ORM\Column(name: 'id_shop', type: 'integer')]
    private int $shopId = 1;

    #[ORM\Column(name: 'categories', type: 'json', nullable: true)]
    private array $categories = [];

    #[ORM\Column(name: 'manufacturers', type: 'json', nullable: true)]
    private array $manufacturers = [];

    #[ORM\Column(name: 'suppliers', type: 'json', nullable: true)]
    private array $suppliers = [];

    #[ORM\Column(name: 'cms_categories', type: 'json', nullable: true)]
    private array $cmsCategories = [];

    #[ORM\Column(name: 'modal', type: 'boolean')]
    private bool $modal = false;

    #[ORM\Column(name: 'delay', type: 'integer')]
    private int $delay = 0;

    #[ORM\Column(name: 'timeout', type: 'integer')]
    private int $timeout = 0;

    #[ORM\Column(name: 'date_start', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $dateStart = null;

    #[ORM\Column(name: 'date_end', type: 'datetime', nullable: true)]
    private ?DateTimeInterface $dateEnd = null;

    #[ORM\Column(name: 'active', type: 'boolean')]
    private bool $active = false;

    #[ORM\OneToMany(mappedBy: 'everblock', targetEntity: EverblockTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true, indexBy: 'languageId')]
    private Collection $translations;

    private ?int $loadedLanguageId = null;

    private ?int $loadedShopId = null;

    public function __construct(?int $id = null, ?int $languageId = null, ?int $shopId = null)
    {
        $this->translations = new ArrayCollection();

        if (null !== $id) {
            $language = $languageId ?? 1;
            $shop = $shopId ?? 1;
            $loaded = self::manager()->getBlock($id, $language, $shop);

            if ($loaded instanceof self) {
                $this->copyFrom($loaded);
                $this->loadedLanguageId = $language;
                $this->loadedShopId = $shop;
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getHookId(): int
    {
        return $this->hookId;
    }

    public function setHookId(int $hookId): void
    {
        $this->hookId = $hookId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }

    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function isOnlyHome(): bool
    {
        return $this->onlyHome;
    }

    public function setOnlyHome(bool $onlyHome): void
    {
        $this->onlyHome = $onlyHome;
    }

    public function isOnlyCategory(): bool
    {
        return $this->onlyCategory;
    }

    public function setOnlyCategory(bool $onlyCategory): void
    {
        $this->onlyCategory = $onlyCategory;
    }

    public function isOnlyCategoryProduct(): bool
    {
        return $this->onlyCategoryProduct;
    }

    public function setOnlyCategoryProduct(bool $onlyCategoryProduct): void
    {
        $this->onlyCategoryProduct = $onlyCategoryProduct;
    }

    public function isOnlyManufacturer(): bool
    {
        return $this->onlyManufacturer;
    }

    public function setOnlyManufacturer(bool $onlyManufacturer): void
    {
        $this->onlyManufacturer = $onlyManufacturer;
    }

    public function isOnlySupplier(): bool
    {
        return $this->onlySupplier;
    }

    public function setOnlySupplier(bool $onlySupplier): void
    {
        $this->onlySupplier = $onlySupplier;
    }

    public function isOnlyCmsCategory(): bool
    {
        return $this->onlyCmsCategory;
    }

    public function setOnlyCmsCategory(bool $onlyCmsCategory): void
    {
        $this->onlyCmsCategory = $onlyCmsCategory;
    }

    public function isObfuscateLink(): bool
    {
        return $this->obfuscateLink;
    }

    public function setObfuscateLink(bool $obfuscateLink): void
    {
        $this->obfuscateLink = $obfuscateLink;
    }

    public function isAddContainer(): bool
    {
        return $this->addContainer;
    }

    public function setAddContainer(bool $addContainer): void
    {
        $this->addContainer = $addContainer;
    }

    public function isLazyload(): bool
    {
        return $this->lazyload;
    }

    public function setLazyload(bool $lazyload): void
    {
        $this->lazyload = $lazyload;
    }

    public function getDevice(): int
    {
        return $this->device;
    }

    public function setDevice(int $device): void
    {
        $this->device = $device;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): void
    {
        $this->background = $background;
    }

    public function getCssClass(): ?string
    {
        return $this->cssClass;
    }

    public function setCssClass(?string $cssClass): void
    {
        $this->cssClass = $cssClass;
    }

    public function getDataAttribute(): ?string
    {
        return $this->dataAttribute;
    }

    public function setDataAttribute(?string $dataAttribute): void
    {
        $this->dataAttribute = $dataAttribute;
    }

    public function getBootstrapClass(): ?string
    {
        return $this->bootstrapClass;
    }

    public function setBootstrapClass(?string $bootstrapClass): void
    {
        $this->bootstrapClass = $bootstrapClass;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function setCategories(array $categories): void
    {
        $this->categories = $categories;
    }

    public function getManufacturers(): array
    {
        return $this->manufacturers;
    }

    public function setManufacturers(array $manufacturers): void
    {
        $this->manufacturers = $manufacturers;
    }

    public function getSuppliers(): array
    {
        return $this->suppliers;
    }

    public function setSuppliers(array $suppliers): void
    {
        $this->suppliers = $suppliers;
    }

    public function getCmsCategories(): array
    {
        return $this->cmsCategories;
    }

    public function setCmsCategories(array $cmsCategories): void
    {
        $this->cmsCategories = $cmsCategories;
    }

    public function isModal(): bool
    {
        return $this->modal;
    }

    public function setModal(bool $modal): void
    {
        $this->modal = $modal;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function getDateStart(): ?DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTimeInterface $dateStart): void
    {
        $this->dateStart = $dateStart;
    }

    public function getDateEnd(): ?DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTimeInterface $dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return Collection<int, EverblockTranslation>
     */
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setContent(int $languageId, ?string $content): void
    {
        $translation = $this->getOrCreateTranslation($languageId);
        $translation->setContent($content);
    }

    public function setCustomCode(int $languageId, ?string $customCode): void
    {
        $translation = $this->getOrCreateTranslation($languageId);
        $translation->setCustomCode($customCode);
    }

    public function getContent(int $languageId): ?string
    {
        $translation = $this->translations->get($languageId);

        return $translation instanceof EverblockTranslation ? $translation->getContent() : null;
    }

    public function getCustomCode(int $languageId): ?string
    {
        $translation = $this->translations->get($languageId);

        return $translation instanceof EverblockTranslation ? $translation->getCustomCode() : null;
    }

    public function duplicateTranslation(int $fromLanguageId, int $toLanguageId): void
    {
        $source = $this->translations->get($fromLanguageId);
        $target = $this->getOrCreateTranslation($toLanguageId);

        if ($source instanceof EverblockTranslation) {
            $target->setContent($source->getContent());
            $target->setCustomCode($source->getCustomCode());
        }
    }

    public function save(): bool
    {
        self::manager()->save($this);

        return true;
    }

    public function __get(string $name)
    {
        if ($name === 'id_everblock' || $name === 'id') {
            return $this->id;
        }

        if ($name === 'content') {
            if (null !== $this->loadedLanguageId) {
                return $this->getContent($this->loadedLanguageId);
            }

            $map = [];
            foreach ($this->translations as $translation) {
                $map[$translation->getLanguageId()] = $translation->getContent();
            }

            return $map;
        }

        if ($name === 'custom_code') {
            if (null !== $this->loadedLanguageId) {
                return $this->getCustomCode($this->loadedLanguageId);
            }

            $map = [];
            foreach ($this->translations as $translation) {
                $map[$translation->getLanguageId()] = $translation->getCustomCode();
            }

            return $map;
        }

        $method = $this->resolveGetter($name);

        if (null !== $method && method_exists($this, $method)) {
            return $this->{$method}();
        }

        return null;
    }

    public function __set(string $name, $value): void
    {
        if ($name === 'content') {
            if (is_array($value)) {
                foreach ($value as $langId => $content) {
                    $this->setContent((int) $langId, $content);
                }
            } elseif (null !== $this->loadedLanguageId) {
                $this->setContent($this->loadedLanguageId, (string) $value);
            }

            return;
        }

        if ($name === 'custom_code') {
            if (is_array($value)) {
                foreach ($value as $langId => $content) {
                    $this->setCustomCode((int) $langId, $content);
                }
            } elseif (null !== $this->loadedLanguageId) {
                $this->setCustomCode($this->loadedLanguageId, (string) $value);
            }

            return;
        }

        $method = $this->resolveSetter($name);

        if (null !== $method && method_exists($this, $method)) {
            $this->{$method}($value);
        }
    }

    public function toArray(int $languageId): array
    {
        return [
            'id_everblock' => $this->id,
            'name' => $this->name,
            'id_hook' => $this->hookId,
            'only_home' => $this->onlyHome,
            'only_category' => $this->onlyCategory,
            'only_category_product' => $this->onlyCategoryProduct,
            'only_manufacturer' => $this->onlyManufacturer,
            'only_supplier' => $this->onlySupplier,
            'only_cms_category' => $this->onlyCmsCategory,
            'obfuscate_link' => $this->obfuscateLink,
            'add_container' => $this->addContainer,
            'lazyload' => $this->lazyload,
            'device' => $this->device,
            'groups' => $this->groups,
            'background' => $this->background,
            'css_class' => $this->cssClass,
            'data_attribute' => $this->dataAttribute,
            'bootstrap_class' => $this->bootstrapClass,
            'position' => $this->position,
            'id_shop' => $this->shopId,
            'categories' => $this->categories,
            'manufacturers' => $this->manufacturers,
            'suppliers' => $this->suppliers,
            'cms_categories' => $this->cmsCategories,
            'modal' => $this->modal,
            'delay' => $this->delay,
            'timeout' => $this->timeout,
            'date_start' => $this->dateStart,
            'date_end' => $this->dateEnd,
            'active' => $this->active,
            'content' => $this->getContent($languageId),
            'custom_code' => $this->getCustomCode($languageId),
        ];
    }

    public function getNormalizedBootstrapClass(): string
    {
        $value = (int) ($this->bootstrapClass ?? 0);

        return self::getBootstrapColClass($value);
    }

    private function getOrCreateTranslation(int $languageId): EverblockTranslation
    {
        $translation = $this->translations->get($languageId);

        if (!$translation instanceof EverblockTranslation) {
            $translation = new EverblockTranslation($this, $languageId);
            $this->translations->set($languageId, $translation);
        }

        return $translation;
    }

    public static function getAllBlocks(int $languageId, int $shopId): array
    {
        $cacheId = sprintf('Everblock_getAllBlocks_%d_%d', $languageId, $shopId);
        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $blocks = self::manager()->listBlocks($shopId, $languageId);
        EverblockCache::cacheStore($cacheId, $blocks);

        return $blocks;
    }

    public static function getBlocks(int $hookId, int $languageId, int $shopId): array
    {
        $cacheId = sprintf('Everblock_getBlocks_%d_%d_%d', $hookId, $languageId, $shopId);
        if (EverblockCache::isCacheStored($cacheId)) {
            return EverblockCache::cacheRetrieve($cacheId);
        }

        $blocks = self::manager()->getBlocksByHook($hookId, $languageId, $shopId);
        EverblockCache::cacheStore($cacheId, $blocks);

        return $blocks;
    }

    public static function cleanBlocksCacheOnDate(int $languageId, int $shopId): void
    {
        self::manager()->cleanCacheOnDate($languageId, $shopId);
    }

    public static function getBootstrapColClass(int $columns): string
    {
        $mapping = [
            1 => '12',
            2 => '6',
            3 => '4',
            4 => '3',
            6 => '2',
        ];

        $default = $mapping[$columns] ?? '12';
        $class = 'col-' . $default . ' col-md-' . ($mapping[$columns] ?? '12');

        return $class;
    }

    private static function manager(): EverblockManager
    {
        if (null === self::$manager) {
            self::$manager = new EverblockManager(DoctrineEntityManagerFactory::createForLegacyContext());
        }

        return self::$manager;
    }

    private function copyFrom(self $source): void
    {
        $this->id = $source->id;
        $this->name = $source->name;
        $this->hookId = $source->hookId;
        $this->onlyHome = $source->onlyHome;
        $this->onlyCategory = $source->onlyCategory;
        $this->onlyCategoryProduct = $source->onlyCategoryProduct;
        $this->onlyManufacturer = $source->onlyManufacturer;
        $this->onlySupplier = $source->onlySupplier;
        $this->onlyCmsCategory = $source->onlyCmsCategory;
        $this->obfuscateLink = $source->obfuscateLink;
        $this->addContainer = $source->addContainer;
        $this->lazyload = $source->lazyload;
        $this->device = $source->device;
        $this->groups = $source->groups;
        $this->background = $source->background;
        $this->cssClass = $source->cssClass;
        $this->dataAttribute = $source->dataAttribute;
        $this->bootstrapClass = $source->bootstrapClass;
        $this->position = $source->position;
        $this->shopId = $source->shopId;
        $this->categories = $source->categories;
        $this->manufacturers = $source->manufacturers;
        $this->suppliers = $source->suppliers;
        $this->cmsCategories = $source->cmsCategories;
        $this->modal = $source->modal;
        $this->delay = $source->delay;
        $this->timeout = $source->timeout;
        $this->dateStart = $source->dateStart;
        $this->dateEnd = $source->dateEnd;
        $this->active = $source->active;

        $this->translations = new ArrayCollection();
        foreach ($source->getTranslations() as $translation) {
            $clone = new EverblockTranslation($this, $translation->getLanguageId());
            $clone->setContent($translation->getContent());
            $clone->setCustomCode($translation->getCustomCode());
            $this->translations->set($translation->getLanguageId(), $clone);
        }
    }

    private function resolveSetter(string $name): ?string
    {
        $map = [
            'id_shop' => 'setShopId',
            'shop_id' => 'setShopId',
            'id_hook' => 'setHookId',
            'hook_id' => 'setHookId',
        ];

        if (isset($map[$name])) {
            return $map[$name];
        }

        $studly = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

        return 'set' . $studly;
    }

    private function resolveGetter(string $name): ?string
    {
        $map = [
            'id_shop' => 'getShopId',
            'shop_id' => 'getShopId',
            'id_hook' => 'getHookId',
            'hook_id' => 'getHookId',
        ];

        if (isset($map[$name])) {
            return $map[$name];
        }

        $studly = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $getter = 'get' . $studly;

        if (method_exists($this, $getter)) {
            return $getter;
        }

        $booleanGetter = 'is' . $studly;

        if (method_exists($this, $booleanGetter)) {
            return $booleanGetter;
        }

        return null;
    }
}

\class_alias(Everblock::class, 'EverBlockClass');
