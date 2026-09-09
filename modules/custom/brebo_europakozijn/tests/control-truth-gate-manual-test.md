# Manual acceptance cases

1. Open Controle with an incomplete situation: price stays blocked and no unresolved technical item is green.
2. Use a valid schema-v4 configuration with a draai-kiep field at 1000 mm: product rules may become green only after the server returns `valid: true`.
3. Change that draai-kiep field to 1001 mm: product rules show `Niet akkoord` and the server message; reliable price stays blocked.
4. Simulate `/europakozijn/api/rules` unavailable: product rules show that server control is unavailable and never turn green.
5. Complete address, floor, room, area and ventilation: wind load, glass/safety and ventilation calculation remain explicitly unresolved; reliable price stays blocked.
