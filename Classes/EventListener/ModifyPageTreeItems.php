<?php

declare(strict_types=1);

namespace Fonda\EmptyPageMark\EventListener;

use Doctrine\DBAL\Exception as DbalException;
use TYPO3\CMS\Backend\Controller\Event\AfterPageTreeItemsPreparedEvent;
use TYPO3\CMS\Backend\Dto\Tree\Status\StatusInformation;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

#[AsEventListener(
    identifier: 'fonda-empty-page-mark/modify-page-tree-items',
)]
final readonly class ModifyPageTreeItems
{
    private const array IGNORED_DOKTYPES = [3, 4, 6, 7199, 254];

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    public function __invoke(AfterPageTreeItemsPreparedEvent $event): void
    {
        $items = $event->getItems();
        foreach ($items as &$item) {
            $pageId = (int)($item['_page']['uid'] ?? 0);
            $doktype = (int)($item['_page']['doktype'] ?? 0);
            if ($pageId === 0 || in_array($doktype, self::IGNORED_DOKTYPES, true) || !$this->isEmpty($pageId)) {
                continue;
            }
            $item['statusInformation'][] = new StatusInformation(
                label: $this->getLanguageService()->sL('LLL:EXT:f_emptyge_mark/Resources/Private/Language/backend.xlf:emptyPage'),
                severity: ContextualFeedbackSeverity::WARNING,
                priority: 0,
                icon: 'actions-exclamation-circle-alt',
                overlayIcon: '',
            );
        }
        unset($item);
        $event->setItems($items);
    }

    private function isEmpty(int $pageId): bool
    {
        try {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
            return (int)$queryBuilder
                ->count('uid')
                ->from('tt_content')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageId, Connection::PARAM_INT)),
                )
                ->executeQuery()
                ->fetchOne() === 0;
        } catch (DbalException) {
            return false;
        }
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
