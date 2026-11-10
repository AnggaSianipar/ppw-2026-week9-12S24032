<?php

namespace App\Http\Controllers\Soap;

use App\Models\Peminjaman;
use Illuminate\Http\Request;

// Adapter legacy: menerjemahkan XML SOAP menjadi query clearance SIPUS-Del.
class LibrarySoapController
{
    public function wsdl()
    {
        $xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<definitions name="SIPUSLibraryService"
    targetNamespace="http://sipus-del.test/soap"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:tns="http://sipus-del.test/soap"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns="http://schemas.xmlsoap.org/wsdl/">
    <message name="CheckClearanceStatusRequest">
        <part name="nim_peminjam" type="xsd:string"/>
    </message>
    <message name="CheckClearanceStatusResponse">
        <part name="active_loans" type="xsd:integer"/>
        <part name="is_cleared" type="xsd:boolean"/>
    </message>
    <portType name="SIPUSLibraryPortType">
        <operation name="CheckClearanceStatus">
            <input message="tns:CheckClearanceStatusRequest"/>
            <output message="tns:CheckClearanceStatusResponse"/>
        </operation>
    </portType>
    <binding name="SIPUSLibraryBinding" type="tns:SIPUSLibraryPortType">
        <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
        <operation name="CheckClearanceStatus">
            <soap:operation soapAction="CheckClearanceStatus"/>
            <input><soap:body use="literal"/></input>
            <output><soap:body use="literal"/></output>
        </operation>
    </binding>
    <service name="SIPUSLibraryService">
        <port name="SIPUSLibraryPort" binding="tns:SIPUSLibraryBinding">
            <soap:address location="/soap/sipus"/>
        </port>
    </service>
</definitions>
XML;

        return response($xml)->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    public function handle(Request $request)
    {
        libxml_use_internal_errors(true);
        $document = simplexml_load_string(
            $request->getContent(),
            \SimpleXMLElement::class,
            LIBXML_NONET | LIBXML_NOBLANKS
        );
        libxml_clear_errors();

        if ($document === false) {
            return $this->fault('soap:Client', 'XML SOAP tidak valid.');
        }

        $namespaces = $document->getDocNamespaces(true);
        $soapNamespace = $namespaces['soap'] ?? 'http://schemas.xmlsoap.org/soap/envelope/';
        $nimNodes = $document->xpath('//*[local-name()="nim_peminjam"]');
        $nim = trim((string) ($nimNodes[0] ?? ''));

        if ($nim === '') {
            return $this->fault('soap:Client', 'nim_peminjam wajib diisi.');
        }

        $activeLoans = Peminjaman::where('nim_peminjam', $nim)
            ->where('status', 'dipinjam')
            ->count();

        $safeNim = htmlspecialchars($nim, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<soap:Envelope xmlns:soap="' . $soapNamespace . '">'
            . '<soap:Body><CheckClearanceStatusResponse>'
            . '<nim_peminjam>' . $safeNim . '</nim_peminjam>'
            . '<active_loans>' . $activeLoans . '</active_loans>'
            . '<is_cleared>' . ($activeLoans === 0 ? 'true' : 'false') . '</is_cleared>'
            . '</CheckClearanceStatusResponse></soap:Body></soap:Envelope>';

        return response($xml)->header('Content-Type', 'text/xml; charset=UTF-8');
    }

    private function fault(string $code, string $message)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">'
            . '<soap:Body><soap:Fault><faultcode>' . $code . '</faultcode>'
            . '<faultstring>' . htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . '</faultstring></soap:Fault></soap:Body></soap:Envelope>';

        return response($xml, 400)->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
