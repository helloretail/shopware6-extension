import header from './header.xml.twig';
import body from './body.xml.twig';
import footer from './footer.xml.twig';

Shopware.Service('helloRetailTemplateService').registerExportTemplate({
    name: 'product',
    headerTemplate: header.trim(),
    bodyTemplate: body,
    footerTemplate: footer.trim(),
    file: null,
    associations: [
        'prices',
        'categories',
        'seoUrls',
        'searchKeywords',
        'manufacturer',
        'media',
        'cover',
        'product.parent',
        'properties.group'
    ]
});
