const { Application } = Shopware;

Application.addServiceProvider('helloRetailTemplateService', () => {
    return {
        registerExportTemplate,
        getExportTemplateByName,
        getExportTemplateRegistry
    };
});

const templateRegistry = {};

function registerExportTemplate(template) {
    templateRegistry[template.name] = template;

    return true;
}

function getExportTemplateByName(name) {
    return templateRegistry[name];
}

function getExportTemplateRegistry() {
    return templateRegistry;
}
