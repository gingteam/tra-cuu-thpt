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
    ->addArgument('year', InputArgument::OPTIONAL, 'Year')
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $curl = new Curl();
        $section1 = $output->section();
        $section2 = $output->section();

        $progressBar = new ProgressBar($section1, 2);
        $progressBar->start();

        $year = $input->getArgument('year') ?? date('Y');
        $curl->get('https://diemthi.vnanet.vn/Home/SearchBySobaodanh?code='.$input->getArgument('SBD').'&nam='.$year);
        $progressBar->advance();
        sleep(1);

        $data = $curl->response;
        $curl->close();

        $progressBar->finish();
        sleep(2);

        if (isset($data->result)) {
            $data = $data->result[0];

            $section1->overwrite('<fg=green>Thành công</>');

            $table1 = new Table($section1);
            $table1
                ->setStyle('box')
                ->setHeaders(['Toán', 'Ngữ văn', 'Ngoại ngữ'])
                ->setRows([
                    [
                        $data->Toan,
                        $data->NguVan,
                        $data->NgoaiNgu,
                    ],
                ]);
            $table1->render();

            $table2 = new Table($section2);
            $table2->setStyle('box');
            if (isset($data->KHTN)) {
                $table2
                    ->setHeaders(['Vật lí', 'Hoá học', 'Sinh học'])
                    ->setRows([
                        [
                            $data->VatLi,
                            $data->HoaHoc,
                            $data->SinhHoc,
                        ],
                    ]);
            } else {
                $table2
                    ->setHeaders(['Địa lí', 'Lịch sử', 'GDCD'])
                    ->setRows([
                        [
                            $data->DiaLi,
                            $data->LichSu,
                            $data->GDCD,
                        ],
                    ]);
            }
            $table2->render();
        } else {
            $section1->overwrite('<fg=red>SBD hoặc năm không không hợp lệ</>');
        }
    })
    ->run();
