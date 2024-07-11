const { resolve, join } = require('path');

module.exports = ({ config }) => {
    // Find the url loader rule
    const urlLoaderRule = config.module.rules.find((rule) => {
        return rule.loader === 'url-loader';
    });

    // Add our svg icons
    urlLoaderRule.exclude.push(
        resolve(join(__dirname, '../src/app/assets/icons/svg'))
    );

    return {
        module: {
            rules: [
                {
                    test: /\.svg$/,
                    include: [
                        resolve(join(__dirname, '../src/app/assets/icons/svg'))
                    ],
                    loader: 'svg-inline-loader',
                    options: {
                        removeSVGTagAttrs: false
                    }
                },
            ]
        }
    };
};