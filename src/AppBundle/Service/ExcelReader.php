<?php 

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExcelReader
{
    /**
     * read
     *
     * @param $file
     * @return array
     */
    public function read($file)
    {
        $data = [];
        
        if (is_string($file)) {
            $filename = $file;
        }
        else if ($file instanceof \SplFileInfo) {
            $filename = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename();
        }
        else {
            return $data;
        }
        
        $objPHPExcel = \PHPExcel_IOFactory::load($filename);
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
        {
            foreach ($worksheet->getRowIterator() as $row)
            {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell)
                {
                    /* @var $cell \PHPExcel_Cell */
                    
                    $row = $cell->getRow();
                    if ($row == 2) /*skip*/ continue;
                    
                    $col = $cell->getColumn();

                    if ($row == 1)
                    {
                        $title = null;
                        if ($col == 'A') {
                            $title = 'Наименование';
                        }
                        else if (!is_null($cell) && !empty($cell->getCalculatedValue())) {
                            $title = $cell->getCalculatedValue();
                        }
                        if (!empty($title)) {
                            $titles[$col] = str_replace("\n", "", $title);
                        }                       
                    }
                    else if ( !is_null($cell) && (!empty($cell->getCalculatedValue()) ) ) {
                        $data[$row][$titles[$col]] = $cell->getCalculatedValue();
                    }
                }
            }
        }

        return $data;
    }

    /**
     * validate
     *
     * @param $data
     * @return mixed
     */
    public function validate($data, $currentRate, $rateHelper)
    {
        $curDate = new \DateTime();
        foreach ($data as $k => $item) {

            if (!isset($data[$k]['Дата Продажи']) || empty($data[$k]['Дата Продажи'])) {
                $data[$k]['Дата Продажи'] = $curDate;
            }
            else {
                $data[$k]['Дата Продажи'] = str_replace(',,',',', $data[$k]['Дата Продажи']);
                $dateSale = \DateTime::createFromformat('d,n,Y', $data[$k]['Дата Продажи']);
                if ($dateSale !== false) {
                    $data[$k]['Дата Продажи'] = $dateSale;
                }
                else {
                    $data[$k]['Дата Продажи'] = $curDate;
                }
            }

            if (isset($data[$k]['Цена, руб.']) && $data[$k]['Цена, руб.'] > 15000) {
                $data[$k]['Цена, руб.'] = $data[$k]['Цена, руб.']/10000;
            }

            if ( (!isset($data[$k]['Стоимость с НДС, руб.']) || empty($data[$k]['Стоимость с НДС, руб.'])) &&
                    isset($data[$k]['Стоим Долл']) ) {
                $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                $data[$k]['Стоимость с НДС, руб.'] = $data[$k]['Стоим Долл'] * $rate;
            }

            if ( (!isset($data[$k]['Стоим Долл']) || empty($data[$k]['Стоим Долл'])) &&
                isset($data[$k]['Стоимость с НДС, руб.']) ) {
                $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                $data[$k]['Стоим Долл'] = $data[$k]['Стоимость с НДС, руб.'] / $rate;
            }

            if ( (!isset($data[$k]['Стоим Бел']) || empty($data[$k]['Стоим Бел'])) &&
                isset($data[$k]['Стоимость с НДС, руб.']) ) {
                $data[$k]['Стоим Бел'] = $data[$k]['Стоимость с НДС, руб.'];
            }

            if (!isset($data[$k]['Стоимость, руб.']) || empty($data[$k]['Стоимость, руб.'])) {
                if (isset($data[$k]['Цена, руб.'])) {
                    $data[$k]['Стоимость, руб.'] = $data[$k]['Цена, руб.']*$data[$k]['Количество'];
                } else if (isset($data[$k]['Стоим Долл']) && !empty($data[$k]['Стоим Долл']) ) {
                    $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                    $data[$k]['Стоимость, руб.'] = $data[$k]['Стоим Долл'] * $rate;
                } else {
                    $data[$k]['Стоимость, руб.'] = 1;
                    //throw new NotFoundHttpException('Not found values(Цена, руб.), on ' . $k . ' row');
                }
            }

            if (!isset($data[$k]['Ставка НДС, %']) && empty($data[$k]['Ставка НДС, %'])) {
                $data[$k]['Ставка НДС, %'] = 10;
            }
            else {
                $data[$k]['Ставка НДС, %'] = (integer) $data[$k]['Ставка НДС, %'];
            }

            if ( (!isset($data[$k]['Цена, руб.']) || empty($data[$k]['Цена, руб.']) ) &&
                isset($data[$k]['Стоимость, руб.'])) {
                $data[$k]['Цена, руб.'] = (
                    (
                        $data[$k]['Стоимость, руб.']
                        - ($data[$k]['Стоимость, руб.']+$data[$k]['Ставка НДС, %'])/($data[$k]['Ставка НДС, %']+100)
                    )
                    )/$data[$k]['Количество'];
            }

            if ( (!isset($data[$k]['Цена, руб.']) || empty($data[$k]['Цена, руб.']) ) &&
                    isset($data[$k]['Стоим Долл'])) {
                $data[$k]['Цена, $'] = (
                            (
                                $data[$k]['Стоим Долл']
                                - ($data[$k]['Стоим Долл']+$data[$k]['Ставка НДС, %'])/($data[$k]['Ставка НДС, %']+100)
                            )
                        )/$data[$k]['Количество'];
                $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                $data[$k]['Цена, руб.'] = $data[$k]['Цена, $']*$rate;
            }

            if (!isset($data[$k]['Стоим Бел']) || empty($data[$k]['Стоим Бел'])) {
                $data[$k]['Стоим Бел'] =
                    // calc vat
                    ($data[$k]['Количество']*$data[$k]['Цена, руб.'])*$data[$k]['Ставка НДС, %']/100
                    // calc selfcost
                    + ($data[$k]['Количество']*$data[$k]['Цена, руб.']);
            }

            if ( (!isset($data[$k]['Стоим Долл']) || empty($data[$k]['Стоим Долл'])) &&
                isset($data[$k]['Стоим Бел']) ) {
                $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                $data[$k]['Стоим Долл'] = $data[$k]['Стоим Бел'] / $rate;
            }

            if (!isset($data[$k]['Количество грузовых мест']) || empty($data[$k]['Количество грузовых мест'])) {
                $data[$k]['Количество грузовых мест'] = (integer) $data[$k]['Количество'];
            }
            elseif ($data[$k]['Количество грузовых мест'] == 'нав.') {
                $data[$k]['Количество грузовых мест'] = $data[$k]['Количество'] * 1;
            }
            else {
                $data[$k]['Количество грузовых мест'] = $data[$k]['Количество грузовых мест'] * 1;
            }

            if (!isset($data[$k]['Масса груза, кг']) || empty($data[$k]['Масса груза, кг'])) {
                $data[$k]['Масса груза, кг'] = 0;
            }

            if ( isset($data[$k]['Продажа бел']) && $data[$k]['Продажа бел']*1 > 15000 ) {
                $data[$k]['Продажа бел'] = $data[$k]['Продажа бел']*1/10000;
            }

            if ( isset($data[$k]['Продажа бел']) || isset($data[$k]['Продажа Долл']) ) {
                if (!isset($data[$k]['Продажа бел']) && isset($data[$k]['Продажа Долл'])) {
                    $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                    $data[$k]['Продажа бел'] = $data[$k]['Продажа Долл'] * $rate;
                }
                if (isset($data[$k]['Продажа бел']) && !isset($data[$k]['Продажа Долл'])) {
                    $rate = $rateHelper->getRate('USD', $data[$k]['Дата Продажи']);
                    $data[$k]['Продажа Долл'] = $data[$k]['Продажа бел'] / $rate;
                }
            }

            if (!isset($data[$k]['Стоим Долл']) || !isset($data[$k]['Стоим Бел']) ||
                !isset($data[$k]['Стоимость, руб.']) || !isset($data[$k]['Ставка НДС, %']) ||
                !isset($data[$k]['Количество']) || !isset($data[$k]['Масса груза, кг']) ||
                !isset($data[$k]['Количество грузовых мест']) )
            {
                throw new NotFoundHttpException('Not found values, on ' . $k . ' row');
                return false;
            }
        }

        return $data;
    }
}