// 备用方案：使用JSONBin.io作为免费的在线数据存储
// 如果你的服务器不支持PHP，可以使用这个方案

class BackupAPI {
    constructor() {
        // 你需要去 https://jsonbin.io 注册并获取API密钥
        // 这里使用的是演示用的公共bin，实际使用时请替换
        this.binId = '6507f9b212a5d376598e1234'; // 替换为你的bin ID
        this.apiKey = '$2a$10$your-api-key-here'; // 替换为你的API密钥
        this.baseUrl = 'https://api.jsonbin.io/v3/b';
    }

    async getSubmissions() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.binId}/latest`, {
                headers: {
                    'X-Master-Key': this.apiKey
                }
            });
            const result = await response.json();
            return { success: true, data: result.record || [] };
        } catch (error) {
            console.error('获取数据失败:', error);
            return { success: false, error: error.message };
        }
    }

    async addSubmission(submission) {
        try {
            // 先获取现有数据
            const current = await this.getSubmissions();
            if (!current.success) {
                throw new Error('无法获取现有数据');
            }

            // 添加新提交
            const newData = [...current.data, {
                ...submission,
                timestamp: new Date().toISOString()
            }];

            // 更新数据
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
            console.error('提交失败:', error);
            return { success: false, error: error.message };
        }
    }

    async clearAll() {
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
            console.error('清空失败:', error);
            return { success: false, error: error.message };
        }
    }
}

// 使用示例：
// const api = new BackupAPI();
// await api.addSubmission({ id: 'S123', transcript: '测试内容' });