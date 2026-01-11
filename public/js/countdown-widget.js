/**
 * Seasonal Countdown Widget Module
 */

class CountdownWidget {
    constructor(element) {
        this.element = element;
        this.season = element.dataset.season;
        this.dataFile = element.dataset.file;
        this.stages = [];
        this.interval = null;

        this.init();
    }

    async init() {
        await this.loadData();
        this.render();
        this.startTimer();
    }

    async loadData() {
        try {
            const response = await fetch(`/${this.dataFile}`);
            if (!response.ok) throw new Error('Failed to load data');
            const data = await response.json();

            // Normalize data format (support both 'name'/'date' and 'etapa'/'estimare')
            this.stages = data.map(item => ({
                name: item.name || item.etapa,
                date: item.date || item.estimare
            }));
        } catch (error) {
            console.error('Error loading countdown data:', error);
            this.showError();
        }
    }

    render() {
        const tbody = this.element.querySelector('.countdown-body');
        if (!tbody || !this.stages.length) return;

        const now = new Date();
        let html = '';

        this.stages.forEach(stage => {
            const targetDate = new Date(stage.date);
            const timeRemaining = this.calculateTimeRemaining(targetDate);

            html += `
                <tr data-stage="${stage.name}">
                    <td>${this.escapeHtml(stage.name)}</td>
                    <td>${this.formatDate(targetDate)}</td>
                    <td class="time-remaining ${this.getUrgencyClass(timeRemaining)}">
                        ${this.formatTimeRemaining(timeRemaining)}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        this.updateNextStage();
    }

    calculateTimeRemaining(targetDate) {
        const now = new Date();
        const diff = targetDate - now;

        if (diff <= 0) {
            return { days: 0, hours: 0, minutes: 0, seconds: 0, total: 0 };
        }

        return {
            days: Math.floor(diff / (1000 * 60 * 60 * 24)),
            hours: Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
            minutes: Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60)),
            seconds: Math.floor((diff % (1000 * 60)) / 1000),
            total: diff
        };
    }

    formatTimeRemaining(time) {
        if (time.total <= 0) {
            return '✓ Trecut';
        }

        const parts = [];
        if (time.days > 0) parts.push(`${time.days}z`);
        if (time.hours > 0) parts.push(`${time.hours}h`);
        if (time.minutes > 0) parts.push(`${time.minutes}m`);
        if (time.days === 0 && time.seconds > 0) parts.push(`${time.seconds}s`);

        return parts.join(' ') || '0s';
    }

    getUrgencyClass(time) {
        if (time.total <= 0) return 'urgent';
        if (time.days === 0) return 'urgent';
        if (time.days < 7) return 'soon';
        return 'normal';
    }

    formatDate(date) {
        const months = ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sep', 'Oct', 'Noi', 'Dec'];
        return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
    }

    updateNextStage() {
        const now = new Date();
        const upcoming = this.stages.find(stage => new Date(stage.date) > now);

        const nextStageText = this.element.querySelector('.next-stage-text');
        if (!nextStageText) return;

        if (upcoming) {
            const timeRemaining = this.calculateTimeRemaining(new Date(upcoming.date));
            nextStageText.textContent = `${upcoming.name} - ${this.formatTimeRemaining(timeRemaining)}`;
        } else {
            nextStageText.textContent = 'Toate etapele au trecut';
        }
    }

    startTimer() {
        // Update every second
        this.interval = setInterval(() => {
            this.render();
        }, 1000);
    }

    showError() {
        const tbody = this.element.querySelector('.countdown-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; color: #dc2626;">Eroare la încărcarea datelor</td></tr>';
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    destroy() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    }
}

// Initialize all countdown widgets on page load
document.addEventListener('DOMContentLoaded', () => {
    const widgets = document.querySelectorAll('.seasonal-widget');
    widgets.forEach(element => {
        new CountdownWidget(element);
    });
});
