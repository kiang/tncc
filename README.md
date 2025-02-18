# Tainan City Council Archive

This repository contains archived data from the Tainan City Council (臺南市議會) in Taiwan, focusing on council motions and related legislative activities.

## Data Source

The data is sourced from the Tainan City Council's official website:
- Motion data: https://bill.tncc.gov.tw/NoPaperMeeting_TNCC/

## Structure

- `/motions/` - Contains JSON files of council motions organized by grade number
  - `/{grade_number}/page_{n}.json` - List of motions per page
  - `/{grade_number}/case_{id}.json` - Detailed information for each motion

## Frontend

The archived data is accessible through a public interface at:
https://tncc.olc.tw/motions/

## Scripts

- `scripts/01_motions_update.php` - Updates the motion archive by fetching data from the council's API

## License

- Software: [MIT License](LICENSE)
- Data: CC BY (Creative Commons Attribution) - Data provided by Tainan City Council
