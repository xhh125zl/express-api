// LabelVisualizer.js - 面单可视化编辑器的前端交互逻辑

class LabelVisualizer {
    constructor(containerId, initialConfig = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            throw new Error('找不到指定的容器元素');
        }
        
        this.config = initialConfig;
        this.selectedField = null;
        this.dragging = false;
        this.resizing = false;
        this.resizeHandle = null;
        this.startPos = { x: 0, y: 0 };
        this.scale = initialConfig.scale || 1.0;
        
        // 初始化事件监听
        this.initEventListeners();
        
        // 如果有配置，渲染面单
        if (initialConfig.template) {
            this.renderLabel(initialConfig.template);
        }
    }
    
    /**
     * 初始化事件监听器
     */
    initEventListeners() {
        // 滚轮缩放
        this.container.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.setScale(this.scale * delta);
        });
        
        // 点击空白区域取消选择
        this.container.addEventListener('click', (e) => {
            if (e.target === this.container) {
                this.deselectField();
            }
        });
    }
    
    /**
     * 设置缩放比例
     */
    setScale(newScale) {
        // 限制缩放范围
        newScale = Math.max(0.5, Math.min(3.0, newScale));
        this.scale = newScale;
        
        const labelElement = this.container.querySelector('.label-preview');
        if (labelElement && this.config.template) {
            const dimensions = this.config.template.dimensions;
            labelElement.style.width = `${dimensions.width * newScale}px`;
            labelElement.style.height = `${dimensions.height * newScale}px`;
            
            // 更新所有字段的位置和大小
            const fields = this.container.querySelectorAll('.field');
            fields.forEach(field => {
                const fieldId = field.dataset.fieldId;
                const fieldData = this.config.template.fields.find(f => f.id === fieldId);
                if (fieldData) {
                    field.style.left = `${fieldData.x * newScale}px`;
                    field.style.top = `${fieldData.y * newScale}px`;
                    field.style.width = `${fieldData.width * newScale}px`;
                    field.style.height = `${fieldData.height * newScale}px`;
                    
                    // 更新字体大小
                    const fontSize = field.querySelector('.field-content, .field-label');
                    if (fontSize) {
                        const originalSize = parseFloat(fontSize.style.fontSize) / newScale;
                        fontSize.style.fontSize = `${originalSize * newScale}px`;
                    }
                }
            });
        }
        
        // 触发缩放事件
        this.triggerEvent('scalechange', { scale: newScale });
    }
    
    /**
     * 渲染面单
     */
    renderLabel(template) {
        this.config.template = template;
        
        // 清空容器
        this.container.innerHTML = '';
        
        // 创建面单容器
        const labelElement = document.createElement('div');
        labelElement.className = 'label-preview';
        labelElement.style.position = 'relative';
        labelElement.style.width = `${template.dimensions.width * this.scale}px`;
        labelElement.style.height = `${template.dimensions.height * this.scale}px`;
        labelElement.style.border = '1px solid #ccc';
        labelElement.style.backgroundColor = '#fff';
        labelElement.style.margin = '20px auto';
        
        // 渲染每个字段
        template.fields.forEach(fieldData => {
            const fieldElement = this.createFieldElement(fieldData);
            labelElement.appendChild(fieldElement);
        });
        
        this.container.appendChild(labelElement);
        
        // 触发渲染完成事件
        this.triggerEvent('rendered', { template });
    }
    
    /**
     * 创建字段元素
     */
    createFieldElement(fieldData) {
        const fieldElement = document.createElement('div');
        fieldElement.className = 'field';
        fieldElement.dataset.fieldId = fieldData.id;
        
        // 设置基本样式
        fieldElement.style.position = 'absolute';
        fieldElement.style.left = `${fieldData.x * this.scale}px`;
        fieldElement.style.top = `${fieldData.y * this.scale}px`;
        fieldElement.style.width = `${fieldData.width * this.scale}px`;
        fieldElement.style.height = `${fieldData.height * this.scale}px`;
        fieldElement.style.fontFamily = fieldData.fontFamily;
        fieldElement.style.fontSize = `${fieldData.fontSize * this.scale}px`;
        fieldElement.style.fontWeight = fieldData.fontWeight;
        fieldElement.style.textAlign = fieldData.align;
        fieldElement.style.color = fieldData.textColor;
        fieldElement.style.border = `${fieldData.borderWidth * this.scale}px solid #666`;
        fieldElement.style.boxSizing = 'border-box';
        fieldElement.style.cursor = 'move';
        fieldElement.style.userSelect = 'none';
        
        // 创建标签
        if (fieldData.showLabel) {
            const labelElement = document.createElement('div');
            labelElement.className = 'field-label';
            labelElement.style.fontSize = `${fieldData.labelFontSize * this.scale}px`;
            labelElement.style.color = fieldData.labelColor;
            labelElement.style.display = 'block';
            labelElement.textContent = fieldData.label;
            fieldElement.appendChild(labelElement);
        }
        
        // 根据字段类型创建内容
        const contentElement = document.createElement('div');
        
        if (fieldData.type === 'barcode') {
            contentElement.className = 'barcode-placeholder';
            contentElement.style.marginTop = `${fieldData.borderWidth * this.scale}px`;
            contentElement.style.textAlign = 'center';
            contentElement.textContent = '[条形码] ' + fieldData.id;
        } else if (fieldData.type === 'qrcode') {
            contentElement.className = 'qrcode-placeholder';
            contentElement.style.margin = 'auto';
            contentElement.style.width = '80%';
            contentElement.style.height = '80%';
            contentElement.style.backgroundColor = '#f0f0f0';
            contentElement.style.display = 'flex';
            contentElement.style.alignItems = 'center';
            contentElement.style.justifyContent = 'center';
            contentElement.textContent = '[二维码]';
        } else {
            contentElement.className = 'field-content';
            contentElement.style.padding = `${fieldData.padding.top * this.scale}px ${fieldData.padding.left * this.scale}px`;
            contentElement.textContent = '{{' + fieldData.id + '}}';
        }
        
        fieldElement.appendChild(contentElement);
        
        // 创建调整大小的句柄
        const resizeHandle = document.createElement('div');
        resizeHandle.className = 'resize-handle';
        resizeHandle.style.position = 'absolute';
        resizeHandle.style.width = '10px';
        resizeHandle.style.height = '10px';
        resizeHandle.style.backgroundColor = '#007bff';
        resizeHandle.style.bottom = '0';
        resizeHandle.style.right = '0';
        resizeHandle.style.cursor = 'se-resize';
        resizeHandle.style.zIndex = '10';
        fieldElement.appendChild(resizeHandle);
        
        // 添加字段事件监听
        this.addFieldEventListeners(fieldElement, fieldData);
        
        return fieldElement;
    }
    
    /**
     * 为字段添加事件监听
     */
    addFieldEventListeners(fieldElement, fieldData) {
        // 选择字段
        fieldElement.addEventListener('click', (e) => {
            e.stopPropagation();
            this.selectField(fieldData.id, fieldElement);
        });
        
        // 开始拖拽
        fieldElement.addEventListener('mousedown', (e) => {
            // 如果点击的是调整大小句柄，不触发拖拽
            if (e.target.classList.contains('resize-handle')) {
                return;
            }
            
            e.stopPropagation();
            this.dragging = true;
            this.selectedField = fieldData.id;
            this.startPos = { x: e.clientX, y: e.clientY };
            
            // 添加选中样式
            this.selectField(fieldData.id, fieldElement);
        });
        
        // 开始调整大小
        const resizeHandle = fieldElement.querySelector('.resize-handle');
        resizeHandle.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            this.resizing = true;
            this.selectedField = fieldData.id;
            this.resizeHandle = resizeHandle;
            this.startPos = { x: e.clientX, y: e.clientY };
            
            // 添加选中样式
            this.selectField(fieldData.id, fieldElement);
        });
    }
    
    /**
     * 选择字段
     */
    selectField(fieldId, fieldElement = null) {
        // 取消之前的选择
        this.deselectField();
        
        // 设置当前选中字段
        this.selectedField = fieldId;
        
        // 添加选中样式
        if (!fieldElement) {
            fieldElement = this.container.querySelector(`.field[data-field-id="${fieldId}"]`);
        }
        
        if (fieldElement) {
            fieldElement.style.boxShadow = '0 0 0 2px #007bff';
            fieldElement.style.zIndex = '5';
        }
        
        // 触发选择事件
        this.triggerEvent('fieldselected', { fieldId });
    }
    
    /**
     * 取消选择字段
     */
    deselectField() {
        if (this.selectedField) {
            const fieldElement = this.container.querySelector(`.field[data-field-id="${this.selectedField}"]`);
            if (fieldElement) {
                fieldElement.style.boxShadow = 'none';
                fieldElement.style.zIndex = '1';
            }
            
            this.selectedField = null;
            this.triggerEvent('fielddeselected');
        }
    }
    
    /**
     * 开始全局鼠标事件监听
     */
    startGlobalEvents() {
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        document.addEventListener('mouseup', this.handleMouseUp.bind(this));
    }
    
    /**
     * 停止全局鼠标事件监听
     */
    stopGlobalEvents() {
        document.removeEventListener('mousemove', this.handleMouseMove.bind(this));
        document.removeEventListener('mouseup', this.handleMouseUp.bind(this));
    }
    
    /**
     * 处理鼠标移动
     */
    handleMouseMove(e) {
        if (this.dragging && this.selectedField) {
            this.moveSelectedField(e.clientX, e.clientY);
        } else if (this.resizing && this.selectedField) {
            this.resizeSelectedField(e.clientX, e.clientY);
        }
    }
    
    /**
     * 处理鼠标释放
     */
    handleMouseUp() {
        this.dragging = false;
        this.resizing = false;
        this.resizeHandle = null;
    }
    
    /**
     * 移动选中的字段
     */
    moveSelectedField(clientX, clientY) {
        const fieldElement = this.container.querySelector(`.field[data-field-id="${this.selectedField}"]`);
        if (!fieldElement || !this.config.template) return;
        
        // 计算移动距离
        const dx = (clientX - this.startPos.x) / this.scale;
        const dy = (clientY - this.startPos.y) / this.scale;
        
        // 更新起始位置
        this.startPos.x = clientX;
        this.startPos.y = clientY;
        
        // 获取字段数据
        const fieldData = this.config.template.fields.find(f => f.id === this.selectedField);
        if (!fieldData) return;
        
        // 计算新位置
        const newX = fieldData.x + dx;
        const newY = fieldData.y + dy;
        
        // 检查边界
        const dimensions = this.config.template.dimensions;
        const newXConstrained = Math.max(0, Math.min(newX, dimensions.width - fieldData.width));
        const newYConstrained = Math.max(0, Math.min(newY, dimensions.height - fieldData.height));
        
        // 更新字段位置
        fieldData.x = newXConstrained;
        fieldData.y = newYConstrained;
        
        // 更新DOM元素位置
        fieldElement.style.left = `${newXConstrained * this.scale}px`;
        fieldElement.style.top = `${newYConstrained * this.scale}px`;
        
        // 触发移动事件
        this.triggerEvent('fieldmoved', { 
            fieldId: this.selectedField, 
            x: newXConstrained, 
            y: newYConstrained 
        });
    }
    
    /**
     * 调整选中字段的大小
     */
    resizeSelectedField(clientX, clientY) {
        const fieldElement = this.container.querySelector(`.field[data-field-id="${this.selectedField}"]`);
        if (!fieldElement || !this.config.template) return;
        
        // 计算大小变化
        const dw = (clientX - this.startPos.x) / this.scale;
        const dh = (clientY - this.startPos.y) / this.scale;
        
        // 更新起始位置
        this.startPos.x = clientX;
        this.startPos.y = clientY;
        
        // 获取字段数据
        const fieldData = this.config.template.fields.find(f => f.id === this.selectedField);
        if (!fieldData) return;
        
        // 计算新尺寸（最小尺寸为10）
        const minSize = 10 / this.scale;
        const newWidth = Math.max(minSize, fieldData.width + dw);
        const newHeight = Math.max(minSize, fieldData.height + dh);
        
        // 检查边界
        const dimensions = this.config.template.dimensions;
        const maxWidth = dimensions.width - fieldData.x;
        const maxHeight = dimensions.height - fieldData.y;
        
        const newWidthConstrained = Math.min(newWidth, maxWidth);
        const newHeightConstrained = Math.min(newHeight, maxHeight);
        
        // 更新字段尺寸
        fieldData.width = newWidthConstrained;
        fieldData.height = newHeightConstrained;
        
        // 更新DOM元素尺寸
        fieldElement.style.width = `${newWidthConstrained * this.scale}px`;
        fieldElement.style.height = `${newHeightConstrained * this.scale}px`;
        
        // 触发大小调整事件
        this.triggerEvent('fieldresized', { 
            fieldId: this.selectedField, 
            width: newWidthConstrained, 
            height: newHeightConstrained 
        });
    }
    
    /**
     * 更新字段属性
     */
    updateFieldProperty(fieldId, property, value) {
        if (!this.config.template) return false;
        
        const fieldData = this.config.template.fields.find(f => f.id === fieldId);
        if (!fieldData) return false;
        
        fieldData[property] = value;
        
        // 更新DOM元素
        const fieldElement = this.container.querySelector(`.field[data-field-id="${fieldId}"]`);
        if (fieldElement) {
            this.updateFieldElementStyle(fieldElement, fieldData);
        }
        
        // 触发更新事件
        this.triggerEvent('fieldupdated', { 
            fieldId, 
            property, 
            value 
        });
        
        return true;
    }
    
    /**
     * 更新字段元素样式
     */
    updateFieldElementStyle(fieldElement, fieldData) {
        // 更新基本样式
        fieldElement.style.fontFamily = fieldData.fontFamily || 'Arial';
        fieldElement.style.fontSize = `${(fieldData.fontSize || 8) * this.scale}px`;
        fieldElement.style.fontWeight = fieldData.fontWeight || 'normal';
        fieldElement.style.textAlign = fieldData.align || 'left';
        fieldElement.style.color = fieldData.textColor || '#000000';
        fieldElement.style.borderWidth = `${(fieldData.borderWidth || 0) * this.scale}px`;
        
        // 更新标签
        const labelElement = fieldElement.querySelector('.field-label');
        if (labelElement) {
            labelElement.style.fontSize = `${(fieldData.labelFontSize || 7) * this.scale}px`;
            labelElement.style.color = fieldData.labelColor || '#666666';
            labelElement.style.display = fieldData.showLabel ? 'block' : 'none';
        }
        
        // 更新内容区域
        const contentElement = fieldElement.querySelector('.field-content');
        if (contentElement && fieldData.padding) {
            contentElement.style.padding = `${(fieldData.padding.top || 0) * this.scale}px ${(fieldData.padding.left || 0) * this.scale}px`;
        }
    }
    
    /**
     * 获取当前模板配置
     */
    getCurrentTemplate() {
        return this.config.template;
    }
    
    /**
     * 触发自定义事件
     */
    triggerEvent(eventName, detail = {}) {
        const event = new CustomEvent(`label:${eventName}`, { 
            bubbles: true, 
            cancelable: true, 
            detail 
        });
        this.container.dispatchEvent(event);
    }
    
    /**
     * 注册事件监听器
     */
    on(eventName, callback) {
        this.container.addEventListener(`label:${eventName}`, callback);
        return this;
    }
    
    /**
     * 移除事件监听器
     */
    off(eventName, callback) {
        this.container.removeEventListener(`label:${eventName}`, callback);
        return this;
    }
}

// 导出类
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = LabelVisualizer;
} else if (typeof window !== 'undefined') {
    window.LabelVisualizer = LabelVisualizer;
}