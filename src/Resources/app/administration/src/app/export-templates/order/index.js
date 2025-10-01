import header from './header.xml.twig';
import body from './body.xml.twig';
import footer from './footer.xml.twig';

Shopware.Service('helloRetailTemplateService').registerExportTemplate({
    name: 'order',
    headerTemplate: header.trim(),
    bodyTemplate: body,
    footerTemplate: footer.trim(),
    file: null,
    associations: [
        'lineItems.product',
        'transactions',
        'transactions.stateMachineState',
        'transactions.paymentMethod',
        'deliveries',
        'deliveries.shippingMethod'
    ]
});