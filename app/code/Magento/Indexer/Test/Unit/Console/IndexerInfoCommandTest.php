<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console;

use Magento\Indexer\Console\IndexerInfoCommand;
use Symfony\Component\Console\Tester\CommandTester;

class IndexerInfoCommandTest extends IndexerCommandCommonTestSetup
{
    /**
     * Command being tested
     *
     * @var IndexerInfoCommand
     */
    private $command;

    public function testGetOptions()
    {
        $this->command = new IndexerInfoCommand($this->objectManagerFactory);
        $this->assertSame([], $this->command->getOptionsList());
    }

    public function testExecute()
    {
        $collection = $this->getMock('Magento\Indexer\Model\Indexer\Collection', [], [], '', false);
        $indexer1 = $this->getMock('Magento\Indexer\Model\Indexer', [], [], '', false);
        $indexer1->expects($this->once())->method('getId')->willReturn('id_indexer1');
        $indexer1->expects($this->once())->method('getTitle')->willReturn('Title_indexer1');
        $collection->expects($this->once())->method('getItems')->willReturn([$indexer1]);

        $this->collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collection));
        $this->indexerFactory->expects($this->never())->method('create');
        $this->command = new IndexerInfoCommand($this->objectManagerFactory);
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
        $actualValue = $commandTester->getDisplay();
        $this->assertSame(sprintf('%-40s %s', 'id_indexer1', 'Title_indexer1') . PHP_EOL, $actualValue);
    }
}
