var component =
    {
        extends: BaseDataComponent,
        mixins: [],
        template: '#template-name#',
        props: [
            "websocketUrl",
            "password"
        ],
        data()
        {
            return {
                rfb: null,
                vncPaste: null,
                desktopName: '',
                sizeUpdateTimer: null,
                websocketUrl: '',
                password: ''
            }
        },
        async mounted()
        {
            this.loadComponentCss('VncConsole', 'css/novnc-style.css');
        },
        created()
        {
            this.$nextTick(async function () {
                await import(this.getUrl('VncConsole', 'js/core/rfb.js')).then(( {default: RFB} ) => {
                    try
                    {
                        this.status("Connecting...");
                        
                        const options = {
                            credentials: {password: this.password}
                        };
                        
                        this.rfb = new RFB(
                            this.$refs.screen,
                            this.websocketUrl,
                            options
                        );
                        
                        // Event listeners
                        this.rfb.addEventListener("connect", this.connectedToServer);
                        this.rfb.addEventListener("disconnect", this.disconnectedFromServer);
                        this.rfb.addEventListener("credentialsrequired", this.credentialsAreRequired);
                        this.rfb.addEventListener("desktopname", this.updateDesktopName);
                        this.rfb.viewOnly = this.readQueryVariable('view_only', false);
                        
                        this.updateSize();
                        this.sizeUpdateTimer = setInterval(this.updateSize, 1000);
                        
                    } catch (e)
                    {
                        this.status(e.message || "Connection error");
                    }
                });
            });
        },
        methods: {
            connectedToServer() {
                this.status("Connected to " + this.desktopName);
                this.initVncPaste()
            },
            
            async initVncPaste()
            {
                const {default: VNCPaste} = await import(this.getUrl('VncConsole', 'js/VNCPaste.js'));
                
                this.vncPaste = new VNCPaste("#noVNC_canvas canvas",);
                this.vncPaste.init();
            },
            
            disconnectedFromServer(e) {
                this.status(e.detail.clean ? "Disconnected" : "Connection lost");
            },
            
            credentialsAreRequired() {
                const password = prompt("Password Required:");
                this.rfb.sendCredentials({ password });
            },
            
            updateDesktopName(e) {
                this.desktopName = e.detail.name;
            },
            
            sendCtrlAltDel() {
                if (this.rfb) {
                    this.rfb.sendCtrlAltDel();
                }
            },
            
            status(text) {
                this.$refs.status.textContent = text;
            },
            
            readQueryVariable(name, defaultValue) {
                const match = document.location.href.match(new RegExp(`[?&]${name}=([^&#]*)`));
                if (match)
                {
                    return decodeURIComponent(match[1]);
                }
                return defaultValue;
            },
            
            updateSize() {
                const screen = this.$refs.screen?.firstElementChild?.firstElementChild;
                const topBar = this.$refs.topBar;
                if (!screen || !topBar) return;
                
                const width = screen.offsetWidth;
                const height = screen.offsetHeight + topBar.offsetHeight;
                const lastFBWidth = Math.floor((width + 1) / 2) * 2;
                const lastFBHeight = Math.floor((height + 1) / 2) * 2;
                const oldSize = this.getWindowSize();
                
                if (lastFBWidth !== oldSize.width || lastFBHeight !== oldSize.height) {
                    try {
                        window.resizeBy(lastFBWidth - oldSize.width, lastFBHeight - oldSize.height);
                    } catch (e) {
                        console.log("Resizing failed:", e);
                    }
                }
            },
            
            getWindowSize() {
                return {
                    width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
                    height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight
                };
            },
            
            getUrl(component, filePath)
            {
                return componentsUrl + component + '/assets/' + filePath
            }
        }
    }