<?php
/**
 * Seasonal Countdown Widget
 */

function renderSeasonalCountdown($widget) {
    $settings = json_decode($widget['settings'], true);
    $season = $settings['season'] ?? 'autumn';
    $data_file = $settings['data_file'] ?? '';

    $season_config = [
        'autumn' => [
            'title' => 'Etape până la toamnă 🍂',
            'theme' => 'autumn-theme',
            'particles' => 'leaves'
        ],
        'christmas' => [
            'title' => 'Etape până la Crăciun 🎄',
            'theme' => 'christmas-theme',
            'particles' => 'snow'
        ]
    ];

    $config = $season_config[$season] ?? $season_config['autumn'];
    ?>

    <div class="seasonal-widget <?php echo e($config['theme']); ?>" data-widget-id="<?php echo $widget['id']; ?>" data-season="<?php echo e($season); ?>" data-file="<?php echo e($data_file); ?>">
        <div class="widget-header">
            <h3 class="widget-title"><?php echo e($config['title']); ?></h3>
        </div>

        <div class="countdown-container">
            <table class="countdown-table">
                <thead>
                    <tr>
                        <th>Etapă</th>
                        <th>Estimare</th>
                        <th>Timp rămas</th>
                    </tr>
                </thead>
                <tbody class="countdown-body">
                    <tr>
                        <td colspan="3" class="loading-state">Se încarcă...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="next-stage-info">
            <strong>Următorul prag:</strong>
            <span class="next-stage-text">Calculare...</span>
        </div>
    </div>

    <style>
        .seasonal-widget {
            background: var(--bg-secondary);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 1px 3px var(--shadow);
        }

        .seasonal-widget:hover {
            box-shadow: 0 2px 8px var(--shadow-hover);
        }

        .autumn-theme {
            border-left: 4px solid #ff8c00;
        }

        .christmas-theme {
            border-left: 4px solid #dc2626;
        }

        .widget-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .widget-title {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin: 0;
            font-weight: 700;
        }

        .autumn-theme .widget-title {
            color: #ff8c00;
        }

        .christmas-theme .widget-title {
            color: #dc2626;
        }

        .countdown-table {
            width: 100%;
            border-collapse: collapse;
        }

        .countdown-table thead {
            background: var(--bg-primary);
        }

        .countdown-table th,
        .countdown-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .countdown-table th {
            color: var(--text-secondary);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .countdown-table td {
            color: var(--text-primary);
            font-size: 0.85rem;
        }

        .countdown-table tr:hover {
            background: var(--bg-tertiary);
        }

        .next-stage-info {
            margin-top: 15px;
            padding: 12px;
            background: var(--bg-primary);
            border-radius: 4px;
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .next-stage-text {
            display: block;
            margin-top: 5px;
            color: var(--accent-red);
            font-weight: 600;
        }

        .loading-state {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
        }

        .time-remaining {
            font-weight: 600;
        }

        .time-remaining.urgent {
            color: #dc2626;
        }

        .time-remaining.soon {
            color: #f59e0b;
        }

        .time-remaining.normal {
            color: #10b981;
        }
    </style>
    <?php
}
