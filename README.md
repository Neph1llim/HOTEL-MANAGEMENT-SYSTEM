# Bella Vista - setup and demo

**Fresh install:** phpMyAdmin > Import `database.sql`, then `demo-data.sql`.
**Already had an older copy:** import `update3.sql` once (keeps your data), then `demo-data.sql` if you want sample data.
**Restart the demo any time:** select the `bellavista` database > Import > `reset-demo.sql` (wipes everything, reloads the sample data with fresh dates).
**Empty system after the defense:** import `clear-all-data.sql`.

Logins: admin / admin123 | maria@demo.com, juan@demo.com, carlo@demo.com, ana@demo.com, liza@demo.com / guest123

## Business rules (for the defense)
- Unpaid hold: an online reservation with no payment proof is cancelled after 24 hours and the room is released (the voucher is returned). A rejected proof restarts the 24 hours. Proofs awaiting review never expire.
- Auto-complete: an approved reservation becomes Completed after check-out time (12:00 NN); 1% of the total is added as points. No admin action needed.
- Refunds: cancelling 3+ days before check-in marks verified payments "refund pending"; the admin records the refund (amount, note) as Refunded. Less than 3 days = downpayment kept.
- Balance: Total minus verified payments. Admin presses "Collect balance" at check-in (recorded as a cash payment).
- Walk-ins: Admin > Walk-in booking; approved immediately, downpayment recorded as cash.
- Rooms: Admin > Rooms & rates (rate, capacity, on/off). New rates apply to future bookings only.
- Rules run automatically whenever any page is opened (no cron job needed). Times use Asia/Manila.
