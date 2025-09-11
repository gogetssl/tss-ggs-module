class VNCPaste {
    constructor(selector = "#noVNC_canvas") {
        this.selector = selector;
        this.canvas = null;
    }
    
    findCanvas() {
        this.canvas = document.querySelector(this.selector);
        if (!this.canvas) throw new Error('The canvas element was not found');
    }
    
    KEY_DELAY = 10;
    SHIFT_NEEDED = /[A-Z!@#$%^&*()_+{}:"<>?~|]/;
    
    async sendKeyboardEvents(char) {
        setTimeout(() => {
            if (char === '\n') {
                this.simulateKeyEvent("keydown", "Enter");
                this.simulateKeyEvent("keyup", "Enter");
            }
            else
            {
                const needsShift = this.SHIFT_NEEDED.test(char);
                
                if (needsShift) {
                    this.simulateKeyEvent("keydown", "Shift", { keyCode: 16 });
                }
                
                this.simulateKeyEvent("keydown", char);
                this.simulateKeyEvent("keyup", char);
                
                if (needsShift) {
                    this.simulateKeyEvent("keyup", "Shift", { keyCode: 16 });
                }
            }
        }, this.KEY_DELAY);
    }
    
    simulateKeyEvent(eventType, key, options = {}) {
        this.canvas.dispatchEvent(
            new KeyboardEvent(eventType, { key, ...options })
        );
    }
    
    async sendString(text) {
        if (!this.canvas) {
            console.error('Canvas not initialized');
            return;
        }
        
        for (let i = 0; i < text.length; i++) {
            await this.sendKeyboardEvents(text[i]);
        }
    }
    
    async handleRightClick(event) {
        if (event.button === 2) {
            event.preventDefault();
            
            navigator.clipboard.readText().then(text => {
                this.sendString(text)
            }).catch(() => {
                let text = prompt("Enter text to paste:");
                if (text != null) window.sendString(text);
            });
        }
    }
    
    init()
    {
        if (this.canvas)
        {
            console.error('VNCPaste is already initialized');
            return;
        }
        
        this.findCanvas();
        this.canvas.addEventListener('mousedown', this.handleRightClick.bind(this));
        window.sendString = this.sendString.bind(this);
    }
}

export default VNCPaste;