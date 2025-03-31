const Encore = require('@symfony/webpack-encore');
const webpack = require('webpack');
const path = require('path');

// Configure l'environnement d'exécution si ce n'est pas déjà fait par la commande "encore"
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableStimulusBridge('./assets/controllers.json')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Active le loader PostCSS, nécessaire pour Tailwind CSS
    .enablePostCssLoader()

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })
    .enableVueLoader(() => {}, { // Un seul appel à enableVueLoader
        version: 3,
        runtimeCompilerBuild: false,
    })
    .enableSassLoader()
    .enableTypeScriptLoader()

    .addPlugin(new webpack.DefinePlugin({
        __VUE_OPTIONS_API__: JSON.stringify(true),
        __VUE_PROD_DEVTOOLS__: JSON.stringify(false),
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: JSON.stringify(false),
    }))

    // Configurer les options de surveillance pour ignorer le répertoire de build
    .configureWatchOptions(options => {
        options.ignored = /public[\\/]build[\\/]/; // Expression régulière robuste
        // options.poll = 1000; // Décommentez si le polling est nécessaire
    })
;

module.exports = Encore.getWebpackConfig();
