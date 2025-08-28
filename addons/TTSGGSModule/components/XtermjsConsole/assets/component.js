var component =
    {
        extends: BaseDataComponent,
        mixins: [],
        template: '#template-name#',
        props: [
            "websocketUrl",
            "commands"
        ],
        data()
        {
            return {
                commands: [],
                websocketUrl: false,
                term: null,
                fitAddon: null
            }
        },
        mounted()
        {
            this.loadComponentScript('XtermjsConsole', 'js/addon-attach.js');
            this.loadComponentScript('XtermjsConsole', 'js/addon-fit.js');
            this.loadComponentScript('XtermjsConsole', 'js/xterm.js');
            this.loadComponentCss('XtermjsConsole', 'css/xterm.css');
        },
        created() {
            this.$nextTick(function () {
                this.waitFor('Terminal').then(() => {
                    this.term = new Terminal();
                    this.fitAddon = new FitAddon.FitAddon();
                    this.term.loadAddon(this.fitAddon);
                    
                    const terminalElement = document.getElementById(this.cid_);
                    this.term.open(terminalElement);
                    this.fitAddon.fit();
                    
                    const socket = new WebSocket(this.websocketUrl);
                    socket.onopen = () => {
                        const attachAddon = new AttachAddon.AttachAddon(socket);
                        this.term.loadAddon(attachAddon);
                        
                        this.commands.forEach(cmd => socket.send(cmd + "\n"));
                    };
                    
                    window.addEventListener("resize", () => this.fitAddon.fit());
                });
            });
        }
    }