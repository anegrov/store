<?php
/**
 * Created by PhpStorm.
 * User: anton
 * Date: 18.7.17
 * Time: 13.51
 */
namespace AppBundle\Command;

use AppBundle\Service\ExcelReader;
use AppBundle\Service\ProductStockChecker;
use AppBundle\Service\RateHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParserCommand extends ContainerAwareCommand
{
    /**
     * configure
     *
     */
    protected function configure()
    {
	ini_set('memory_limit', '2048M');
	set_time_limit(0);

        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:parser')
            ->addArgument('filename', InputArgument::REQUIRED, 'Path to file')
            ->addArgument('doc', InputArgument::REQUIRED, 'Have documents?')
            // the short description shown while running "php bin/console list"
            ->setDescription('Parse xls')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to parse a xls document...');
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get USD rate
        $rateHelper = $this->getContainer()->get(RateHelper::class);
        if ( ! ($rate = $rateHelper->getRate('USD') ) ) {
            $output->writeln('Rate not found!');
            exit("error");
        }

        $filename = $input->getArgument('filename');
        $doc = $input->getArgument('doc');

        // read excel file uploaded by user
        $excelReader = $this->getContainer()->get(ExcelReader::class);
        $data = $excelReader->read($filename);
        $data = $excelReader->validate($data, $rate, $rateHelper);

        // get USD rate
//        if ( ! ($data = $rateHelper->applyCurrencyToArray('USD', $data, ['Цена, руб.', 'Стоимость, руб.'], ['Цена, $', 'Стоимость, $'])) )
//        {
//            exit("error");
//        }

        $checker = $this->getContainer()->get(ProductStockChecker::class);
        $checkedData = $checker->check($data);

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $productRepo = $em->getRepository('AppBundle:ProductStock');

        list($countNewProductInserted, $countOldProductInserted, $countBunchCreated)
            = $productRepo->saveCheckedData($checkedData, null, $doc, $rate);

        // outputs multiple lines to the console (adding "\n" at the end of each line)
        $output->writeln('new Product: ' . $countNewProductInserted);
        $output->writeln('new Bunch: ' . $countBunchCreated);
        $output->writeln('added old product in stock: ' . $countOldProductInserted);

        // outputs a message followed by a "\n"
        $output->writeln('Whoa!');

        // outputs a message without adding a "\n" at the end of the line
        $output->write('You are about to ');
        $output->writeln('create a products.');
    }
}
