<?php

namespace App\Http\Controllers\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class LibrarySoapController extends Controller
{
    /**
     * 1. Menampilkan kontrak WSDL
     * GET /soap/sipus.wsdl
     */
    public function wsdl(): Response
    {
        $endpointUrl = url('/soap/sipus');

        $wsdlXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="LibrarySoapService"
    targetNamespace="http://sipus.del.ac.id/soap"
    xmlns:tns="http://sipus.del.ac.id/soap"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns="http://schemas.xmlsoap.org/wsdl/">

    <!-- Parameter permintaan -->
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>

    <!-- Parameter hasil -->
    <message name="CheckClearanceStatusResponse">
        <part name="active_loans" type="xsd:int"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>

    <!-- Operasi SOAP -->
    <portType name="LibraryPortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>

    <!-- Pengaturan SOAP -->
    <binding name="LibrarySoapBinding"
        type="tns:LibraryPortType">

        <soap:binding
            style="rpc"
            transport="http://schemas.xmlsoap.org/soap/http"/>

        <operation name="CheckClearanceStatus">
            <soap:operation
                soapAction="http://sipus.del.ac.id/soap#CheckClearanceStatus"/>

            <input>
                <soap:body use="literal"
                    namespace="http://sipus.del.ac.id/soap"/>
            </input>

            <output>
                <soap:body use="literal"
                    namespace="http://sipus.del.ac.id/soap"/>
            </output>
        </operation>
    </binding>

    <!-- Alamat layanan SOAP -->
    <service name="LibrarySoapService">
        <port name="LibraryPort"
            binding="tns:LibrarySoapBinding">

            <soap:address location="{$endpointUrl}"/>
        </port>
    </service>
</definitions>
XML;

        return response($wsdlXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * 2. Memproses permintaan SOAP
     * POST /soap/sipus
     */
    public function handle(Request $request): Response
    {
        // Mengambil isi XML dari request
        $rawXml = trim($request->getContent());

        // Memeriksa apakah XML kosong
        if (empty($rawXml)) {
            return $this->soapFault(
                'soap:Client',
                'Body XML SOAP tidak boleh kosong.'
            );
        }

        // Membaca XML
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string(
            $rawXml,
            'SimpleXMLElement',
            LIBXML_NONET
        );

        // Memeriksa apakah format XML valid
        if ($xml === false) {
            libxml_clear_errors();

            return $this->soapFault(
                'soap:Client',
                'Format XML tidak valid.'
            );
        }

        // Mengambil NIM mahasiswa dari SOAP
        $nimNodes = $xml->xpath(
            '//*[local-name()="CheckClearanceStatus"]/*[local-name()="nim_peminjam"] | //*[local-name()="CheckClearanceStatusRequest"]/*[local-name()="nim_peminjam"]'
        );

        // Memeriksa apakah NIM kosong
        if (
            empty($nimNodes) ||
            trim((string) $nimNodes[0]) === ''
        ) {
            return $this->soapFault(
                'soap:Client',
                'NIM peminjam tidak boleh kosong.'
            );
        }

        $nim = trim((string) $nimNodes[0]);

        // Menghitung jumlah pinjaman aktif
        $activeLoans = DB::table('peminjamans')
            ->where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        // Menentukan status bebas pustaka
        $isCleared = $activeLoans === 0
            ? 'true'
            : 'false';

        // Membuat respons SOAP XML
        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:m="http://sipus.del.ac.id/soap">

    <soap:Body>
        <m:CheckClearanceStatusResponse>
            <m:active_loans>{$activeLoans}</m:active_loans>
            <m:is_cleared>{$isCleared}</m:is_cleared>
        </m:CheckClearanceStatusResponse>
    </soap:Body>

</soap:Envelope>
XML;

        return response($responseXml, 200)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    /**
     * 3. Menampilkan pesan kesalahan SOAP
     */
    private function soapFault(
        string $faultCode,
        string $faultMessage
    ): Response {

        // Mengamankan pesan kesalahan untuk XML
        $safeMessage = htmlspecialchars(
            $faultMessage,
            ENT_XML1,
            'UTF-8'
        );

        // Membuat SOAP Fault
        $faultXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope
    xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">

    <soap:Body>
        <soap:Fault>
            <faultcode>{$faultCode}</faultcode>
            <faultstring>{$safeMessage}</faultstring>
        </soap:Fault>
    </soap:Body>

</soap:Envelope>
XML;

        return response($faultXml, 500)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
