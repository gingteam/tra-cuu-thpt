#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Curl\Curl;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->setName('Lookup')
    ->setVersion('1.0.0')
    ->addArgument('SBD', InputArgument::REQUIRED, 'So Bao Danh')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $curl = new Curl();
        $section1 = $output->section();
        $section2 = $output->section();

        $progressBar = new ProgressBar($section1, 2);
        $progressBar->start();
        $curl->get('https://diemthi.vnanet.vn/Home/SearchBySobaodanh?code='.((int) $input->getArgument('SBD')).'&nam='.date('Y'));
        $progressBar->advance();
        sleep(1);
        $data = json_decode($curl->response, true);

        $curl->close();
        $progressBar->finish();
        sleep(2);

        if ($data['result']) {
            $section1->overwrite('<fg=green>Thành công</>');
            $res = $data['result'][0];
            $tohop = $res['KHTN'] ? ['Vật lí', 'Hoá học', 'Sinh học'] : ['Địa lí', 'Lịch sử', 'GDCD'];
            $table1 = new Table($section1);
            $table1
                ->setStyle('box')
                ->setHeaders(['Toán', 'Ngữ văn', 'Ngoại ngữ'])
                ->setRows([
                    [
                        $res['Toan'],
                        $res['NguVan'],
                        $res['NgoaiNgu'],
                    ],
                ]);
            $table1->render();

            $table2 = new Table($section2);
            $table2->setStyle('box');
            if ($res['KHTN']) {
                $table2
                    ->setHeaders(['Vật lí', 'Hoá học', 'Sinh học'])
                    ->setRows([
                        [
                            $res['VatLi'],
                            $res['HoaHoc'],
                            $res['SinhHoc'],
                        ],
                    ]);
            } else {
                $table2
                    ->setHeaders(['Địa lí', 'Lịch sử', 'GDCD'])
                    ->setRows([
                        [
                            $res['DiaLi'],
                            $res['LichSu'],
                            $res['GDCD'],
                        ],
                    ]);
            }
            $table2->render();
        } else {
            $section1->overwrite('<fg=red>SBD không hợp lệ</>');
        }
    })
    ->run();
