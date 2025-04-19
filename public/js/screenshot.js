function generateFoodItineraryImage(data) {
    // Canvas setup
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    // Configuration
    const config = {
        dayHeaderHeight: 60,
        timeHeaderHeight: 40,
        itemHeight: 120,
        itemMargin: 8,
        canvasWidth: 500,
        padding: 20,
        colors: {
            background: '#1a1d24',
            dayHeader: '#1a1d24',
            dayText: '#ffffff',
            timeText: {
                'sáng': '#ff6b81',
                'trưa': '#ff6b81',
                'chiều': '#ff6b81',
                'tối': '#ff6b81'
            },
            locationBg: '#2a2e36',
            locationBorder: '#3a3f48',
            locationName: '#ffffff',
            locationAddress: '#4ecdc4',
            locationDesc: '#bbbbbb',
            buttonBg: {
                map: '#ff6b81',
                save: '#4ecdc4',
                skip: '#6c5ce7'
            },
            buttonText: '#ffffff'
        },
        fonts: {
            dayHeader: 'bold 22px Arial',
            timeHeader: 'bold 18px Arial',
            locationName: 'bold 16px Arial',
            locationAddress: '14px Arial',
            locationDesc: '14px Arial',
            button: 'bold 12px Arial'
        }
    };

    // Calculate canvas height based on content
    let totalHeight = config.padding;
    const dayKeys = Object.keys(data);

    // First pass: calculate total height
    for (const dayKey of dayKeys) {
        const day = data[dayKey];
        totalHeight += config.dayHeaderHeight;

        const timeKeys = ['sáng', 'trưa', 'chiều', 'tối', 'Danh sách yêu thích gần đây'].filter(time => day[time] && day[time].length > 0);

        for (const timeKey of timeKeys) {
            totalHeight += config.timeHeaderHeight;
            totalHeight += day[timeKey].length * (config.itemHeight + config.itemMargin);
        }
    }
    totalHeight += config.padding; // Bottom padding

    // Set canvas dimensions
    canvas.width = config.canvasWidth;
    canvas.height = totalHeight;

    // Fill background
    ctx.fillStyle = config.colors.background;
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Draw content
    let yPos = config.padding;

    for (const dayKey of dayKeys) {
        const day = data[dayKey];

        // Draw day header
        ctx.fillStyle = config.colors.dayHeader;
        ctx.fillRect(config.padding, yPos, canvas.width - (config.padding * 2), config.dayHeaderHeight);

        ctx.fillStyle = config.colors.dayText;
        ctx.font = config.fonts.dayHeader;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'middle';
        ctx.fillText(dayKey.toUpperCase(), config.padding * 2, yPos + (config.dayHeaderHeight / 2));

        yPos += config.dayHeaderHeight;

        // Process time periods in a specific order
        const timeOrder = ['sáng', 'trưa', 'chiều', 'tối', 'Danh sách yêu thích gần đây'];

        for (const timeKey of timeOrder) {
            if (!day[timeKey] || day[timeKey].length === 0) continue;

            // Draw time header
            ctx.fillStyle = config.colors.timeText[timeKey];
            ctx.font = config.fonts.timeHeader;
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(`◉ ${timeKey.toUpperCase()}`, config.padding * 2, yPos + (config.timeHeaderHeight / 2));

            yPos += config.timeHeaderHeight;

            // Draw locations for this time period
            for (const location of day[timeKey]) {
                // Draw location card background
                ctx.fillStyle = config.colors.locationBg;
                roundRect(ctx, config.padding, yPos, canvas.width - (config.padding * 2), config.itemHeight, 8, true, false);

                // Draw cyan dot
                ctx.fillStyle = config.colors.locationAddress;
                ctx.beginPath();
                ctx.arc(config.padding * 2, yPos + 28, 6, 0, Math.PI * 2);
                ctx.fill();

                // Draw location name
                ctx.fillStyle = config.colors.locationName;
                ctx.font = config.fonts.locationName;
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(location.name, config.padding * 3 + 10, yPos + 28);

                // Draw location address
                ctx.fillStyle = config.colors.locationAddress;
                ctx.font = config.fonts.locationAddress;
                ctx.fillText(location.address, config.padding * 3 + 10, yPos + 52);

                // Draw location description
                ctx.fillStyle = config.colors.locationDesc;
                ctx.font = config.fonts.locationDesc;

                // Handle multiline description
                const maxWidth = canvas.width - (config.padding * 5);
                const words = location.description.split(' ');
                let line = '';
                let descY = yPos + 76;

                for (let i = 0; i < words.length; i++) {
                    const testLine = line + words[i] + ' ';
                    const metrics = ctx.measureText(testLine);

                    if (metrics.width > maxWidth && i > 0) {
                        ctx.fillText(line, config.padding * 3 + 10, descY);
                        line = words[i] + ' ';
                        descY += 20;
                    } else {
                        line = testLine;
                    }
                }
                ctx.fillText(line, config.padding * 3 + 10, descY);

                // Draw buttons
                const btnWidth = 70;
                const btnHeight = 30;
                const btnSpacing = 10;
                const btnY = yPos + config.itemHeight - btnHeight - 10;
                const btnStartX = config.padding * 2;

                // // Map button
                // ctx.fillStyle = config.colors.buttonBg.map;
                // roundRect(ctx, btnStartX, btnY, btnWidth, btnHeight, 5, true, false);
                // ctx.fillStyle = config.colors.buttonText;
                // ctx.font = config.fonts.button;
                // ctx.textAlign = 'center';
                // ctx.fillText('Map', btnStartX + (btnWidth / 2), btnY + (btnHeight / 2));

                // // Save button
                // ctx.fillStyle = config.colors.buttonBg.save;
                // roundRect(ctx, btnStartX + btnWidth + btnSpacing, btnY, btnWidth, btnHeight, 5, true, false);
                // ctx.fillStyle = config.colors.buttonText;
                // ctx.fillText('Saved', btnStartX + btnWidth + btnSpacing + (btnWidth / 2), btnY + (btnHeight / 2));

                // // Skip button
                // ctx.fillStyle = config.colors.buttonBg.skip;
                // roundRect(ctx, btnStartX + (btnWidth * 2) + (btnSpacing * 2), btnY, btnWidth, btnHeight, 5, true, false);
                // ctx.fillStyle = config.colors.buttonText;
                // ctx.fillText('Skip', btnStartX + (btnWidth * 2) + (btnSpacing * 2) + (btnWidth / 2), btnY + (btnHeight / 2));

                // Increment Y position for next item
                yPos += config.itemHeight + config.itemMargin;
            }

            // Add "Thêm mới" button for this time section
            // ctx.strokeStyle = '#3f51b5';
            // ctx.lineWidth = 2;
            // roundRect(ctx, config.padding, yPos, canvas.width - (config.padding * 2), 40, 8, false, true);

            // ctx.fillStyle = '#3f51b5';
            // ctx.font = config.fonts.locationName;
            // ctx.textAlign = 'center';
            // ctx.textBaseline = 'middle';
            // ctx.fillText('Thêm mới', canvas.width / 2, yPos + 20);

            yPos += 5 + config.itemMargin;
        }
    }

    // Helper function for rounded rectangles
    function roundRect(ctx, x, y, width, height, radius, fill, stroke) {
        if (typeof radius === 'undefined') {
            radius = 5;
        }
        if (typeof radius === 'number') {
            radius = { tl: radius, tr: radius, br: radius, bl: radius };
        } else {
            var defaultRadius = { tl: 0, tr: 0, br: 0, bl: 0 };
            for (var side in defaultRadius) {
                radius[side] = radius[side] || defaultRadius[side];
            }
        }
        ctx.beginPath();
        ctx.moveTo(x + radius.tl, y);
        ctx.lineTo(x + width - radius.tr, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + radius.tr);
        ctx.lineTo(x + width, y + height - radius.br);
        ctx.quadraticCurveTo(x + width, y + height, x + width - radius.br, y + height);
        ctx.lineTo(x + radius.bl, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - radius.bl);
        ctx.lineTo(x, y + radius.tl);
        ctx.quadraticCurveTo(x, y, x + radius.tl, y);
        ctx.closePath();
        if (fill) {
            ctx.fill();
        }
        if (stroke) {
            ctx.stroke();
        }
    }

    // Return the canvas element and also export the image right away
    return {
        canvas: canvas,
        dataUrl: canvas.toDataURL('image/png'),
        downloadImage: function (filename = 'food-itinerary.png') {
            const link = document.createElement('a');
            link.download = filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    };
}
