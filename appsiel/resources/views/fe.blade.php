<soapenv:Header>
    <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wsswssecurity-secext-1.0.xsd">
        <wsse:UsernameToken>
            <wsse:Username>
                8ac82326-3016-430f-8d69-9efc4bcefd8f
            </wsse:Username>
            <wsse:Password>
                6361b7b5322acb07ced00a35a85a4cc5183da3a42ede0b07f578067a18425a55
            </wsse:Password>
            <wsse:Nonce>
                FmbZRkx1jh2A+imgjD2fLQ==
            </wsse:Nonce>
            <wsu:Created>
                2015-07-31T16:34:33.762Z
            </wsu:Created>
        </wsse:UsernameToken>
    </wsse:Security>
</soapenv:Header>


<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<fe:Invoice xmlns:fe="http://www.dian.gov.co/contratos/facturaelectronica/v1"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:clm54217="urn:un:unece:uncefact:codelist:specification:54217:2001"
xmlns:clm66411="urn:un:unece:uncefact:codelist:specification:66411:2001"
xmlns:clmIANAMIMEMediaType="urn:un:unece:uncefact:codelist:specification:IANAMIMEMediaTy
pe:2003"
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures"
xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.dian.gov.co/contratos/facturaelectronica/v1
http://www.dian.gov.co/micrositios/fac_electronica/documentos/XSD/r0/DIAN_UBL.xsd">
<ext:UBLExtensions>
<ext:UBLExtension>
<ext:ExtensionContent/>
</ext:UBLExtension>
</ext:UBLExtensions>
<!--<cbc:UBLVersionID>UBL 2.0</cbc:UBLVersionID>-->
<cbc:UBLVersionID/>
<cbc:CustomizationID/>
<cbc:ProfileID/>
<cbc:ID/>
<cbc:UUID/>
<cbc:IssueDate>1957-08-13</cbc:IssueDate>
<cbc:IssueTime>09:30:47+05:00</cbc:IssueTime>
<cbc:InvoiceTypeCode/>
<cbc:Note>&#182;Estos fragmentos ejemplifican el uso de los elementos relacionados en el
nombre del archivo, y de los /fe:Invoice/cbc:Note[X] que siguen a continuación; tal como está
conformado pasa satisfactoriamente la validación del motor XSD, pero esto no indica que se trata
de una 'factura electrónica de Colombia' en estado EXITOSO. Solo ilustra el uso de los elementos y
sus atributos que han sido referenciados.</cbc:Note>
<cbc:Note>&#182;Este archivo ejemplifica el uso del elemento
/fe:Invoice/fe:AccountingSupplierParty/cbc:AdditionalAccountID y los efectos en
/fe:Invoice/fe:AccountingSupplierParty y en /fe:Invoice/fe:AccountingCustomerParty</cbc:Note>
<cbc:DocumentCurrencyCode/>
<cac:OrderReference>
<cbc:ID/>
</cac:OrderReference>
<fe:AccountingSupplierParty>
<!--Invoice__AdditionalAccountID_supplier_3d3f-s.xml
&#13;Invoice__AdditionalAccountID_supplier_2d3f-s.xml
&#13;Invoice__AdditionalAccountID_supplier_1d3f-s.xml
&#13;Invoice__AdditionalAccountID.lst.html-->
<cbc:AdditionalAccountID schemeName="tipos de organización jurídica; vendedor: una persona
jurídica. Solo use el valor '1'"
schemeDataURI="http://www.dian.gov.co">1</cbc:AdditionalAccountID>
<fe:Party>
<cac:PartyIdentification>
<cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y
Aduanas Nacionales)" schemeID="31" schemeName="identificación sin DV del mandatario;
identificación de quien obtuvo el código de activación registrado en el xPath
/fe:Invoice/ext:UBLExtensions/ext:UBLExtension/ext:ExtensionContent/sts:DianExtensions/sts:Soft
wareProvider/sts:SoftwareID"
schemeDataURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico
_001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf 'Tipos de documentos de
identidad'">900373125</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>nombre establecimiento del punto de venta || nombre mercantil del punto de venta
|| nombre del establecimiento de comercio y en su defecto nombre del comerciante según los
Arts. 10 y 20 del Código de Comercio
<!--Revise fragmento
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[5]/cbc:TaxLevelCode/@li
stName-->
<!--&#13;Casilla 162 del RUT-->
</cbc:Name>
</cac:PartyName>
<fe:PhysicalLocation>
<cbc:ID schemeDataURI="http://www.rues.org.co/RUES_Web/" schemeName="matrícula
mercantil">matrícula mercantil
<!--&#13;Casilla 160 del RUT-->
</cbc:ID>
<cbc:Description>ubicación del punto de venta || localización || guía para llegar</cbc:Description>
<fe:Address>
<cbc:Department>Departamento de Colombia</cbc:Department>
<cbc:CitySubdivisionName>Barrio || Localidad</cbc:CitySubdivisionName>
<cbc:CityName>Ciudad || Municipio</cbc:CityName>
<cac:AddressLine>
<cbc:Line>Dirección</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode>CO</cbc:IdentificationCode>
</cac:Country>
</fe:Address>
</fe:PhysicalLocation>
<fe:PartyTaxScheme>
<!--Fragmentos de información Tributaria, Aduanera y Cambiaria, i.e. TAC; DIAN y organismos
tributarios departamentales, regionales y municipales-->
<!--para facilitar operaciones de reconocimiento de créditos / débitos fiscales, incluya un fragmento
con
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1]/cbc:RegistrationName
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1]/cbc:CompanyID
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1]/cbc:TaxLevelCode
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1]/cac:RegistrationAddre
ss
&#13;/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1]/cac:TaxScheme
-->
<!-- El fragmento
/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1..n]/cbc:TaxLevelCode se
repetirá tantas veces ("n") como la cantidad de valores registrados en el RUT. - Observe la
conformación de otros fragmentos más adelante. -->
<!-- vea el archivo Invoice__AdditionalAccountID.lst.html -->
<cbc:RegistrationName>nombre del vendedor / mandatario / contribuyente en el RUT / nombre del
comerciante según los Arts. 10 y 20 del Código de Comercio
<!-- -->
</cbc:RegistrationName>
<cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos
y Aduanas Nacionales)" schemeID="31" schemeName="NIT con DV del vendedor "
schemeDataURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf"
schemeVersionID="'Tipos de documentos de identidad'">900373125-9
<!--número del NIT con DV-->
<!--casillas 5 y 6 del RUT-->
<!--útil para trámites de créditos fiscales-->
</cbc:CompanyID>
<cbc:TaxLevelCode listName="TIPOS OBLIGACIONES-RESPONSABILIDADES:2016"
listSchemeURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_
001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf" name="O-11: Ventas
régimen común">O-11
<!-- Valores de la casilla 53 del RUT-->
<!-- Valores de la casilla 54 del RUT -->
<!-- El fragmento
/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1..n]/cbc:TaxLevelCode se
repetirá tantas veces ("n") como la cantidad de valores registrados en el RUT. - Observe la
conformación de otros fragmentos más adelante. -->
<!-- vea el archivo Invoice__AdditionalAccountID.lst.html -->
</cbc:TaxLevelCode>
<cac:RegistrationAddress>
<cbc:CitySubdivisionName>barrio del comerciante</cbc:CitySubdivisionName>
<cbc:CityName>ciudad del comerciante. Casilla 40 del RUT</cbc:CityName>
<cbc:PostalZone>zona postal del comerciante</cbc:PostalZone>
<cbc:CountrySubentity>departamento del comerciante. Casilla 39 del RUT</cbc:CountrySubentity>
<cac:AddressLine>
<cbc:Line>dirección del comerciante. Casilla 41 del RUT</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode listID="ISO 3166-1">CO</cbc:IdentificationCode>
<cbc:Name/>
<!--País. Casilla 38 del RUT-->
</cac:Country>
</cac:RegistrationAddress>
<cac:TaxScheme/>
</fe:PartyTaxScheme>
<fe:PartyTaxScheme>
<!-- /fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[2]/cbc:TaxLevelCode -->
<!-- El fragmento
/fe:Invoice/fe:AccountingSupplierParty/fe:Party/fe:PartyTaxScheme[1..n]/cbc:TaxLevelCode se
repetirá tantas veces ("n") como la cantidad de valores registrados en el RUT. - Observe la
conformación de otros fragmentos más adelante. -->
<!-- vea el archivo Invoice__AdditionalAccountID.lst.html -->
<cbc:TaxLevelCode listName="TIPOS PERSONA:2016"
listSchemeURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_
001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf" name="Persona
Jurídica">1</cbc:TaxLevelCode>
<cac:TaxScheme/>
</fe:PartyTaxScheme>
<fe:PartyTaxScheme>
<cbc:TaxLevelCode listName="TIPOS OBLIGACIONES-RESPONSABILIDADES:2016"
listSchemeURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_
001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf" name="Gran
contribuyente">O-13</cbc:TaxLevelCode>
<cac:TaxScheme/>
</fe:PartyTaxScheme>
<fe:PartyTaxScheme>
<cbc:TaxLevelCode listName="TIPOS OBLIGACIONES-RESPONSABILIDADES:2016"
listSchemeURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_
001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf" name="Facturación
Electrónica Voluntaria Modelo 2242">O-38</cbc:TaxLevelCode>
<cac:TaxScheme/>
</fe:PartyTaxScheme>
<fe:PartyTaxScheme>
<cbc:TaxLevelCode listName="TIPOS ESTABLECIMIENTO:2016"
listSchemeURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico_
001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf" name="Punto de
venta">17</cbc:TaxLevelCode>
<!--Casilla 160 del RUT-->
<cac:TaxScheme/>
</fe:PartyTaxScheme>
<fe:PartyLegalEntity>
<!--Información de la Cámara de Comercio-->
<!--si el elemento /fe:Invoice/fe:AccountingSupplierParty/cbc:AdditionalAccountID = 1 entonces
este fragmento es obligatorio-->
<cbc:RegistrationName>nombre del comerciante en la Cámara de Comercio o
equivalente</cbc:RegistrationName>
<cbc:CompanyID schemeDataURI="http://www.rues.org.co/RUES_Web/" schemeName="matrícula
mercantil">número matrícula mercantil de la empresa</cbc:CompanyID>
<cac:RegistrationAddress>
<cbc:CitySubdivisionName>barrio del comerciante en Cámara de
Comercio</cbc:CitySubdivisionName>
<cbc:CityName>ciudad del comerciante en Cámara de Comercio</cbc:CityName>
<cbc:PostalZone>zona postal del comerciante en Cámara de Comercio</cbc:PostalZone>
<cbc:CountrySubentity>departamento del comerciante en Cámara de
Comercio</cbc:CountrySubentity>
<cac:AddressLine>
<cbc:Line>dirección del comerciante en Cámara de Comercio</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode listID="ISO 3166-1">CO</cbc:IdentificationCode>
<cbc:Name></cbc:Name>
<!--en Cámara de Comercio-->
</cac:Country>
</cac:RegistrationAddress>
</fe:PartyLegalEntity>
</fe:Party>
</fe:AccountingSupplierParty>
<fe:AccountingCustomerParty>
<cbc:AdditionalAccountID schemeName="tipos organización jurídica; comprador: una persona
jurídica; solo use el valor 1"
schemeDataURI="http://www.dian.gov.co/micrositios/fac_electronica/documentos/Anexo_Tecnico
_001_Formatos_de_los_Documentos_XML_de_Facturacion_Electron.pdf">1<!--El valor 1 implica el
uso obligatorio del fragmento
/fe:Invoice/fe:AccountingCustomerParty/fe:Party/fe:PartyLegalEntity/cbc:RegistrationName-->
</cbc:AdditionalAccountID>
<fe:Party>
<cac:PartyIdentification>
<cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Direccion de Impuestos y
Aduanas Nacionales)" schemeID="31" schemeName="31:= NIT;">123456789<!--sin DV-->
<!--fundamento: vea el literal 'c' del Art. 617 del E.T.-->
</cbc:ID>
</cac:PartyIdentification>
<cac:PartyName>
<cbc:Name>Arthur Street Calle 220<!--nombre de establecimiento de comercio; elemento
optativo;-->
<!--casilla 165 del formulario 001 de la DIAN: RUT-->
<!--cuando no se requiere, puede omitirse el elemento
/fe:Invoice/fe:AccountingCustomerParty/fe:Party/cac:PartyName-->
</cbc:Name>
</cac:PartyName>
<fe:PhysicalLocation>
<!--fragmento obligatorio; puede estar vacío-->
<!--casillas 163, 164 y 165 del RUT-->
</fe:PhysicalLocation>
<fe:PartyTaxScheme>
<!--fragmento obligatorio--><!--diligenciamiento será útil para trámites de créditos fiscales--><!--se
repite el fragmento de acuerdo con la cantidad de elementos de las casillas 53, 54 y 160 del RUT--
><!-- vea el archivo Invoice__AdditionalAccountID.lst.html -->
<cbc:TaxLevelCode>
<!--fragmento obligatorio; puede estar vacío-->
</cbc:TaxLevelCode>
<cac:TaxScheme>
<!--fragmento obligatorio; puede estar vacío-->
</cac:TaxScheme>
</fe:PartyTaxScheme>
<fe:PartyLegalEntity>
<cbc:RegistrationName>Arthur Street<!--nombre de persona jurídica; elemento obligatorio;-->
<!--fundamento: vea el literal 'c' del Art. 617 del E.T.-->
</cbc:RegistrationName>
<cbc:CompanyID schemeDataURI="http://www.rues.org.co/RUES_Web/" schemeName="matrícula
mercantil">Número matrícula mercantil de la empresa<!--fragmento optativo--><!--número del NIT
con DV--><!--casillas 5 y 6 del RUT--><!--útil para trámites de créditos fiscales--><!-- vea el archivo
Invoice__AdditionalAccountID.lst.html -->
</cbc:CompanyID>
<cac:RegistrationAddress>
<!--fragmento optativo-->
<!--casilla 38, 39, 40 y 41 del RUT-->
</cac:RegistrationAddress>
</fe:PartyLegalEntity>
</fe:Party>
</fe:AccountingCustomerParty>
<cac:PayeeParty/>
<fe:PrepaidPayment>
<cbc:ID xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">normalizedString</cbc:ID>
<cbc:PaidAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:PaidAmount>
<cbc:PaidDate xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">1957-08-13</cbc:PaidDate>
</fe:PrepaidPayment>
<fe:TaxTotal>
<cbc:TaxAmount xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2"
currencyID="AED">0</cbc:TaxAmount>
<cbc:RoundingAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:RoundingAmount>
<cbc:TaxEvidenceIndicator
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">true</cbc:TaxEvidenceIndicator>
<fe:TaxSubtotal xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">
<TaxableAmount currencyID="AED">0</TaxableAmount>
<TaxAmount currencyID="AED">0</TaxAmount>
<Percent>0</Percent>
<cac:TaxCategory>
<cac:TaxScheme/>
</cac:TaxCategory>
</fe:TaxSubtotal>
</fe:TaxTotal>
<fe:LegalMonetaryTotal>
<cbc:LineExtensionAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:LineExtensionAmount>
<cbc:TaxExclusiveAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:TaxExclusiveAmount>
<cbc:AllowanceTotalAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:AllowanceTotalAmount>
<cbc:ChargeTotalAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:ChargeTotalAmount>
<cbc:PayableAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:PayableAmount>
</fe:LegalMonetaryTotal>
<fe:InvoiceLine>
<cbc:ID xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">normalizedString</cbc:ID>
<cbc:InvoicedQuantity
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">0</cbc:InvoicedQuantity>
<cbc:LineExtensionAmount
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
currencyID="AED">0</cbc:LineExtensionAmount>
<cbc:AccountingCost
xmlns="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents2">String</cbc:AccountingCost>
<fe:Item xmlns="http://www.dian.gov.co/contratos/facturaelectronica/v1"/>
<fe:Price xmlns="http://www.dian.gov.co/contratos/facturaelectronica/v1">
<cbc:PriceAmount currencyID="AED">0</cbc:PriceAmount>
</fe:Price>
</fe:InvoiceLine>
</fe:Invoice>





/*      Ejemplo de petición usando Base64 */

POST /B2BIntegrationEngine/FacturaElectronica HTTP/1.1
Accept-Encoding: gzip,deflate
Content-Type: text/xml;charset=UTF-8
SOAPAction: ""
Content-Length: 3342
Host: 192.168.250.65:9080
Connection: Keep-Alive
User-Agent: Apache-HttpClient/4.1.1 (java 1.5)
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:rep="http://www.dian.gov.co/servicios/facturaelectronica/ReportarFactura">
<soapenv:Header>
 <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wsswssecurity-secext-1.0.xsd">
 <wsse:UsernameToken>
 <wsse:Username>8ac82326-3016-430f-8d69-9efc4bcefd8f</wsse:Username>

<wsse:Password>6361b7b5322acb07ced00a35a85a4cc5183da3a42ede0b07f578067a18425a55</
wsse:Password>
 <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wsssoap-message-security-1.0#Base64Binary">FmbZRkx1jh2A+imgjD2fLQ==</wsse:Nonce>
 <wsu:Created>2015-10-06T12:00:33.762Z</wsu:Created>
 </wsse:UsernameToken>
 </wsse:Security>
 </soapenv:Header>
 <soapenv:Body>
 <rep:EnvioFacturaElectronicaPeticion>
 <rep:NIT>900184680</rep:NIT>
 <rep:InvoiceNumber>1001</rep:InvoiceNumber>
 <rep:IssueDate>2015-07-16T00:00:00</rep:IssueDate>

<rep:Document>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
BQYAAAAAAQABAD0AAADDBgAAAAA=</rep:Document>
 </rep:EnvioFacturaElectronicaPeticion>
 </soapenv:Body>
</soapenv:Envelope>
