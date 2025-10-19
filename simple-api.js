// 简单的纯前端数据存储解决方案
// 使用JSONBin.io免费服务

class SimpleAPI {
    constructor() {
        // 使用JSONBin.io的免费服务
        // 你需要去 https://jsonbin.io 注册获取API密钥
        this.binId = '67139a8bad19ca34f8c8f123'; // 这是一个示例ID，请替换为你自己的
        this.apiKey = '$2a$10$VqeGqz9YrXxQqGqGqGqGqOeGqGqGqGqGqGqGqGqGqGqGqGqGqGqGqG'; // 请替换为你的API密钥
        this.baseUrl = 'https://api.jsonbin.io/v3/b';
        
        // 如果没有配置API密钥，使用localStorage作为备用
        this.useLocalStorage = !this.apiKey || this.apiKey.includes('your-api-key');
    }

    async getSubmissions() {
        if (this.useLocalStorage) {
            // 使用localStorage作为备用方案
            const data = localStorage.getItem('studentSubmissions');
            return { 
                success: true, 
                data: data ? JSON.parse(data) : [] 
            };
        }

        try {
            const response = await fetch(`${this.baseUrl}/${this.binId}/latest`, {
                headers: {
                    'X-Master-Key': this.apiKey
                }
            });
            
            if (!response.ok) {
                throw new Error('网络请求失败');
            }
            
            const result = await response.json();
            return { 
                success: true, 
                data: result.record || [] 
            };
        } catch (error) {
            console.error('获取数据失败，使用本地存储:', error);
            // 降级到localStorage
            const data = localStorage.getItem('studentSubmissions');
            return { 
                success: true, 
                data: data ? JSON.parse(data) : [] 
            };
        }
    }

    async addSubmission(submission) {
        const newSubmission = {
            ...submission,
            timestamp: new Date().toISOString()
        };

        if (this.useLocalStorage) {
            // 使用localStorage作为备用方案
            const current = await this.getSubmissions();
            const newData = [...current.data, newSubmission];
            localStorage.setItem('studentSubmissions', JSON.stringify(newData));
            
            // 触发storage事件，通知其他标签页
            window.dispatchEvent(new StorageEvent('storage', {
                key: 'studentSubmissions',
                newValue: JSON.stringify(newData)
            }));
            
            return { success: true, message: '提交成功（本地存储）' };
        }

        try {
            // 先获取现有数据
            const current = await this.getSubmissions();
            if (!current.success) {
                throw new Error('无法获取现有数据');
            }

            // 添加新提交
            const newData = [...current.data, newSubmission];

            // 更新到JSONBin
            const response = await fetch(`${this.baseUrl}/${this.binId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Master-Key': this.apiKey
                },
                body: JSON.stringify(newData)
            });

            if (response.ok) {
                return { success: true, message: '提交成功' };
            } else {
                throw new Error('提交失败');
            }
        } catch (error) {
            console.error('在线提交失败，使用本地存储:', error);
            // 降级到localStorage
            const current = await this.getSubmissions();
            const newData = [...current.data, newSubmission];
            localStorage.setItem('studentSubmissions', JSON.stringify(newData));
            return { success: true, message: '提交成功（本地存储）' };
        }
    }

    async clearAll() {
        if (this.useLocalStorage) {
            localStorage.removeItem('studentSubmissions');
            return { success: true, message: '清空成功（本地存储）' };
        }

        try {
            const response = await fetch(`${this.baseUrl}/${this.binId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Master-Key': this.apiKey
                },
                body: JSON.stringify([])
            });

            if (response.ok) {
                return { success: true, message: '清空成功' };
            } else {
                throw new Error('清空失败');
            }
        } catch (error) {
            console.error('在线清空失败，使用本地存储:', error);
            localStorage.removeItem('studentSubmissions');
            return { success: true, message: '清空成功（本地存储）' };
        }
    }
}

// 创建全局API实例
window.simpleAPI = new SimpleAPI();