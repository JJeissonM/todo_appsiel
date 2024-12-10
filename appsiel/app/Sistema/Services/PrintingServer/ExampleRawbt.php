<?php

namespace App\Sistema\Services\PrintingServer;

use App\Models\PrintingJob;
use Exception;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Mike42\Escpos\EscposImage;

use Mike42\Escpos\PrintConnectors\RawbtPrintConnector;
use Mike42\Escpos\CapabilityProfile;

class ExampleRawbt
{
    
    public function feed_paper( $line_numbers )
    {
        try {
            $profile = CapabilityProfile::load("POS-5890");
        
            /* Fill in your own connector here */
            $connector = new RawbtPrintConnector();

            $printer = new Printer($connector, $profile);
        
            $printer->feed( $line_numbers );
        

        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $printer->close();
        }

    }
    public function feed_reverse_paper( $line_numbers )
    {
        try {
            $profile = CapabilityProfile::load("POS-5890");
        
            /* Fill in your own connector here */
            $connector = new RawbtPrintConnector();

            $printer = new Printer($connector, $profile);
        
            $printer->feedReverse( $line_numbers * -1 );        

        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $printer->close();
        }

    }

    public function print2()
    {
        try {
            $profile = CapabilityProfile::load("POS-5890");
        
            /* Fill in your own connector here */
            $connector = new RawbtPrintConnector();
        
            /* Information for the receipt */
            $items = array(
                new Line("Example item #1", "4.00"),
                new Line("Another thing", "3.50"),
                new Line("Something else", "1.00"),
                new Line("A final item", "4.45"),
            );
            $subtotal = new Line('Subtotal', '12.95');
            $tax = new Line('A local tax', '1.30');
            $total = new Line('Total', '14.25', true);
            /* Date is kept the same for testing */
            // $date = date('l jS \of F Y h:i:s A');
            $date = "Monday 6th of April 2015 02:56:25 PM";
        
            /* Start the printer */
            $logo = EscposImage::load(storage_path() . '/app/logos_empresas/companypng-6713be1ada04a.png', false);
            $printer = new Printer($connector, $profile);
        
        
            /* Print top logo */
            if ($profile->getSupportsGraphics()) {
                $printer->graphics($logo);
            }
            if ($profile->getSupportsBitImageRaster() && !$profile->getSupportsGraphics()) {
                $printer->bitImage($logo);
            }
        
            /* Name of shop */
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text("ExampleMart Ltd.\n");
            $printer->selectPrintMode();
            $printer->text("Shop No. 42.\n");
            $printer->feed();
        
        
            /* Title of receipt */
            $printer->setEmphasis(true);
            $printer->text("SALES INVOICE\n");
            $printer->setEmphasis(false);
        
            /* Items */
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis(true);
            $printer->text(new Line('', '$'));
            $printer->setEmphasis(false);
            foreach ($items as $item) {
                $printer->text($item->getAsString(32)); // for 58mm Font A
            }
            $printer->setEmphasis(true);
            $printer->text($subtotal->getAsString(32));
            $printer->setEmphasis(false);
            $printer->feed();
        
            /* Tax and total */
            $printer->text($tax->getAsString(32));
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text($total->getAsString(32));
            $printer->selectPrintMode();
        
            /* Footer */
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank you for shopping\n");
            $printer->text("at ExampleMart\n");
            $printer->text("For trading hours,\n");
            $printer->text("please visit example.com\n");
            $printer->feed(2);
            $printer->text($date . "\n");
        
            /* Barcode Default look */
        
            $printer->barcode("ABC", Printer::BARCODE_CODE39);
            $printer->feed();
            $printer->feed();
        
        
            // Demo that alignment QRcode is the same as text
            $printer2 = new Printer($connector); // dirty printer profile hack !!
            $printer2->setJustification(Printer::JUSTIFY_CENTER);
            $printer2->qrCode("https://rawbt.ru/mike42", Printer::QR_ECLEVEL_M, 8);
            $printer2->text("rawbt.ru/mike42\n");
            $printer2->setJustification();
            $printer2->feed();
        
        
            /* Cut the receipt and open the cash drawer */
            $printer->cut();
            $printer->pulse();

        } catch (Exception $e) {
            echo $e->getMessage();
        } finally {
            $printer->close();
        }
    }
}
