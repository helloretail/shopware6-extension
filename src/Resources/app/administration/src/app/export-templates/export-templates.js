export default (() => {
    const context = require.context('./twig', false, /twig$/);

    context.keys().map((item) => {
        const name = item.split('.')[1].split('/')[1];

        Shopware.Service('helloRetailTemplateService').registerExportTemplate({
            name: name,
            template: context(item)
        });
    })
})();
