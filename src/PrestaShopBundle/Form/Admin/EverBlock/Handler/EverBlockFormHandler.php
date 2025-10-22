<?php
/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 */

namespace Everblock\PrestaShopBundle\Form\Admin\EverBlock\Handler;

use Everblock\PrestaShopBundle\Form\Admin\EverBlock\Command\Handler\UpsertEverBlockHandler;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\Command\UpsertEverBlockCommand;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\DataProvider\EverBlockFormDataProvider;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\Dto\EverBlockData;
use Everblock\PrestaShopBundle\Form\Admin\EverBlock\EverBlockType;
use Everblock\Tools\Service\EverblockTools;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class EverBlockFormHandler
{
    private $formFactory;

    private $dataProvider;

    private $commandHandler;

    public function __construct(
        FormFactoryInterface $formFactory,
        EverBlockFormDataProvider $dataProvider,
        UpsertEverBlockHandler $commandHandler
    ) {
        $this->formFactory = $formFactory;
        $this->dataProvider = $dataProvider;
        $this->commandHandler = $commandHandler;
    }

    public function handle(array $rawData): EverBlockFormHandlerResult
    {
        $blockId = isset($rawData['id_everblock']) && $rawData['id_everblock'] !== '' ? (int) $rawData['id_everblock'] : null;
        $formData = $this->dataProvider->getData($blockId);
        $form = $this->formFactory->create(EverBlockType::class, $formData, $this->dataProvider->getFormOptions());
        $normalized = $this->dataProvider->normalizeRequestData($rawData, $blockId);
        $form->submit($normalized, false);

        if (!$form->isSubmitted()) {
            return new EverBlockFormHandlerResult(false, $blockId, ['Form was not submitted.']);
        }

        if (!$form->isValid()) {
            return new EverBlockFormHandlerResult(false, $blockId, $this->getFormErrors($form));
        }

        $data = $form->getData();
        $everBlockData = EverBlockData::fromArray($data);
        $convertedContent = [];
        foreach ($everBlockData->getContent() as $langId => $content) {
            $convertedContent[$langId] = EverblockTools::convertImagesToWebP($content);
        }
        $everBlockData = $everBlockData->withContent($convertedContent);

        $command = new UpsertEverBlockCommand(
            $everBlockData->getId(),
            $everBlockData->getShopId(),
            $everBlockData->getName(),
            $everBlockData->getHookId(),
            $everBlockData->getContent(),
            $everBlockData->getCustomCode(),
            $everBlockData->isActive(),
            $everBlockData->getDevice(),
            $this->encodeJson($everBlockData->getGroupIds()),
            $everBlockData->isOnlyHome(),
            $everBlockData->isOnlyCategory(),
            $everBlockData->isOnlyCategoryProduct(),
            $this->encodeJson($everBlockData->getCategoryIds()),
            $everBlockData->isOnlyManufacturer(),
            $this->encodeJson($everBlockData->getManufacturerIds()),
            $everBlockData->isOnlySupplier(),
            $this->encodeJson($everBlockData->getSupplierIds()),
            $everBlockData->isOnlyCmsCategory(),
            $this->encodeJson($everBlockData->getCmsCategoryIds()),
            $everBlockData->isObfuscateLink(),
            $everBlockData->isAddContainer(),
            $everBlockData->isLazyload(),
            $this->formatNullableString($everBlockData->getBackground()),
            $this->formatNullableString($everBlockData->getCssClass()),
            $this->formatNullableString($everBlockData->getDataAttribute()),
            $everBlockData->getBootstrapClass(),
            $everBlockData->getPosition(),
            $everBlockData->isModal(),
            $this->formatNullableInt($everBlockData->getDelay()),
            $this->formatNullableInt($everBlockData->getTimeout()),
            $this->formatDate($everBlockData->getDateStart()),
            $this->formatDate($everBlockData->getDateEnd())
        );

        try {
            $id = $this->commandHandler->handle($command);
        } catch (\Throwable $exception) {
            return new EverBlockFormHandlerResult(false, $blockId, [$exception->getMessage()]);
        }

        return new EverBlockFormHandlerResult(true, $id);
    }

    private function getFormErrors(FormInterface $form): array
    {
        $messages = [];
        foreach ($form->getErrors(true) as $error) {
            $messages[] = $error->getMessage();
        }

        return array_values(array_unique($messages));
    }

    private function encodeJson(array $values): string
    {
        return json_encode(array_values($values));
    }

    private function formatNullableString($value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    private function formatNullableInt($value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return (int) $value;
    }

    private function formatDate($value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return null;
    }
}
