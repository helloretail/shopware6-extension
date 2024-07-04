import header from './header.xml.twig';
import body from './body.xml.twig';
import footer from './footer.xml.twig';

Shopware.Service('helloRetailTemplateService').registerExportTemplate({
    name: 'category',
    headerTemplate: header.trim(),
    bodyTemplate: body,
    footerTemplate: footer.trim(),
    file: 'categories.xml',
    associations: [
        'products'
    ]
});
