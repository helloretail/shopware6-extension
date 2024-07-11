const { Component } = Shopware;

Component.override('sw-icon-deprecated', {
    beforeMount() {
        if (this.name === 'svg-hello-retail') {
            this.iconSvgData = `<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                 width="24.000000pt" height="24.000000pt" viewBox="0 0 24.000000 24.000000">

                                <g transform="translate(0.000000,24.000000) scale(0.100000,-0.100000)"
                                fill="#758CA3" fill-rule="evenodd" stroke="none">
                                <path d="M75 231 c-45 -20 -70 -60 -70 -112 0 -42 5 -53 33 -81 28 -28 39 -33
                                82 -33 43 0 54 5 82 33 28 28 33 39 33 82 0 42 -5 54 -31 81 -33 33 -92 46
                                -129 30z m95 -95 c0 -38 -21 -66 -50 -66 -10 0 -26 7 -34 16 -36 35 -7 84 50
                                84 32 0 34 -2 34 -34z"/>
                                </g>
                                </svg>`
        }
    },
})