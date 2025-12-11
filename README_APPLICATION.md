# Surveying Monitoring Application (Überwachungs Programm)

A Laravel-based web application for monitoring surveying measurements, tracking deviations over time, and generating comprehensive PDF reports. This application replicates the functionality of the Python-based surveying monitoring tool with a modern web interface.

## Features

### Project Management
- Create and manage multiple surveying projects
- Store project information (project number, name, editor/bearbeiter)
- Configure threshold values for warnings, cautions, and alarms

### Data Import
- **Null Measurements (Baseline)**: Import CSV files containing initial reference measurements
- **Control Measurements (Monitoring)**: Import multiple CSV files with monitoring data over time
- Support for multiple CSV formats and encodings (UTF-8, ISO-8859-1, Windows-1252)
- CSV Format: `Punkt,E,N,H,Datum` (Point, Easting, Northing, Height, Date)

### Data Analysis
- Calculate deviations from null (baseline) measurements
- Calculate deviations from previous measurements
- Compute 2D position shifts (ΔE, ΔN, ΔL)
- Compute height shifts (ΔH)
- Color-coded threshold-based alerts:
  - 🟨 Yellow: Warning level (Aufmerksamkeitswert)
  - 🟧 Orange: Caution level (Interventionswert)
  - 🟥 Red: Alarm level (Alarmwert)

### Visualizations
- Interactive deviation tables showing:
  - Comparison to null measurement
  - Comparison to previous measurement
  - All deviations in millimeters
- Chart support for:
  - ΔE and ΔN per point over time
  - 2D position shift per point over time
  - ΔH per point over time
  - Vector displacement plot (XY)

### Reporting
- Generate comprehensive PDF reports including:
  - Project information and metadata
  - Threshold values
  - Deviation tables for all monitoring points
  - Color-coded alerts
  - Date and editor information

## Installation

### Requirements
- PHP 8.1 or higher
- Composer
- SQLite (included) or MySQL/PostgreSQL

### Setup

1. Clone the repository:
```bash
git clone https://github.com/skatecram/surveying_monitoring.git
cd surveying_monitoring
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start the development server:
```bash
php artisan serve
```

6. Access the application at `http://localhost:8000`

## Usage

### Creating a Project

1. Navigate to the application homepage
2. Click "Neues Projekt" (New Project)
3. Fill in:
   - Auftragsnummer (Project Number)
   - Auftrag/Projektname (Project Name)
   - Bearbeiter (Editor/Processor)
4. Click "Erstellen" (Create)

### Importing Measurements

#### Null Measurement (Baseline)
1. Open a project
2. In the "Import" tab, under "Nullmessung"
3. Click "Choose File" and select your CSV file
4. Click "Importieren" (Import)
5. The application will replace any existing null measurements

#### Control Measurements (Monitoring Data)
1. In the same "Import" tab, under "Kontrollmessungen"
2. Click "Choose File" and select your CSV file
3. Click "Importieren" (Import)
4. Multiple imports will accumulate measurements over time

### CSV File Format

Your CSV files should follow this format:
```
Point,Easting,Northing,Height,Date
P1,2500000.123,1200000.456,450.123,2024-01-15
P2,2500010.234,1200005.567,451.234,2024-01-15
P3,2500020.345,1200010.678,452.345,2024-01-15
```

Supported date formats: `YYYY-MM-DD`, `DD.MM.YYYY`, `DD/MM/YYYY`, `MM/DD/YYYY`

### Viewing Deviation Tables

1. Switch to the "Tabelle" (Table) tab
2. Review the threshold values (can be edited in project settings)
3. View deviation tables for each monitoring point:
   - Comparison to null measurement (with color coding)
   - Comparison to previous measurement
   - All values in millimeters (mm)

### Generating PDF Reports

1. Ensure you have imported both null and control measurements
2. Navigate to the "Tabelle" tab
3. Click "PDF-Bericht erstellen" (Create PDF Report)
4. The PDF will be generated and downloaded automatically

## Database Schema

### Projects Table
- `id`: Primary key
- `number`: Project number
- `name`: Project name
- `bearbeiter`: Editor/processor name
- `threshold_warning`: Warning threshold (default: 3.0 mm)
- `threshold_caution`: Caution threshold (default: 5.0 mm)
- `threshold_alarm`: Alarm threshold (default: 10.0 mm)
- `created_at`, `updated_at`: Timestamps

### Null Measurements Table
- `id`: Primary key
- `project_id`: Foreign key to projects
- `punkt`: Monitoring point identifier
- `E`: Easting coordinate
- `N`: Northing coordinate
- `H`: Height
- `date`: Measurement date
- `created_at`, `updated_at`: Timestamps

### Control Measurements Table
- Same structure as null_measurements table
- Can have multiple measurements per point over time

## Technical Stack

- **Framework**: Laravel 12
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **PDF Generation**: DomPDF
- **Frontend**: Tailwind CSS (CDN)
- **Charts**: Chart.js (for visualizations)
- **PHP**: 8.3+

## Comparison to Original Python Application

This Laravel application replicates all core functionality of the original Python/PyQt5 application:

| Feature | Python App | Laravel App |
|---------|-----------|-------------|
| Project Management | ✅ JSON files | ✅ Database |
| CSV Import | ✅ | ✅ |
| Null Measurements | ✅ | ✅ |
| Control Measurements | ✅ | ✅ |
| Deviation Calculations | ✅ | ✅ |
| Threshold Alerts | ✅ | ✅ |
| Deviation Tables | ✅ | ✅ |
| PDF Reports | ✅ | ✅ |
| Charts/Diagrams | ✅ Matplotlib | ✅ Chart.js |
| Multi-user | ❌ Desktop only | ✅ Web-based |
| Data Persistence | JSON files | Database |

## Screenshots

### Project List
![Projects List](https://github.com/user-attachments/assets/aa0fec36-60c9-4180-ace4-40319829a6ad)

### Create New Project
![Create Project](https://github.com/user-attachments/assets/fb20c200-1b45-4a0e-be94-16bbfdb1b2d0)

### Project View with Data Import
![Project View](https://github.com/user-attachments/assets/61692086-2eb4-495d-a616-7f08f00b7483)

### Deviation Tables
![Deviation Tables](https://github.com/user-attachments/assets/3dc29b62-81cc-46c5-9deb-0eea0c3b4cc8)

## License

This project is part of a portfolio/demonstration application.

## Contributing

This is a demonstration project. For production use, consider:
- Adding user authentication
- Implementing role-based access control
- Adding data validation and error handling
- Enhancing chart interactivity
- Adding export to Excel
- Implementing backup/restore functionality
