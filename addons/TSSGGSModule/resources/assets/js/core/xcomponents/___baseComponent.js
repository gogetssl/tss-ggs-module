const BaseComponent = {
    components: vueComponents,
    mixins: [
        TranslatorMixin
    ],
    props: [
        'cid',
        'namespace',
        'uniq'
    ],
    data: function () {
        return {
            uniq_: null,
        }
    },
    created()
    {
    },
    computed: {
        cid_: {
            get()
            {
                return this.cid;
            },
            set(newValue)
            {
            }
        }
    },
    methods: {
        loadComponentScript(component, path, callback)
        {
            let script = document.createElement('script')
            script.setAttribute('src', componentsUrl + component + '/assets/' + path);
            script.setAttribute("id", "loader-" + component);
            script.addEventListener('load', callback);
            
            document.head.appendChild(script)
        },
        waitFor(objectPath, interval = 100)
        {
            return new Promise((resolve) => {
                const checkAvailability = setInterval(() => {
                    const obj = objectPath.split('.').reduce((o, i) => (o ? o[i] : undefined), window);
                    if (obj)
                    {
                        clearInterval(checkAvailability);
                        resolve(obj);
                    }
                }, interval);
            });
        },
        loadComponentCss(component, path)
        {
            let style = document.createElement('link')
            style.setAttribute('href', componentsUrl + component + '/assets/' + path);
            style.setAttribute('rel', 'stylesheet')
            document.head.appendChild(style)
        }
    }
};