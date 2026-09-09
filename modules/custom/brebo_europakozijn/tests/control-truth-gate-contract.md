# Europakozijn control truth gate contract

Regression contract for the public technical-control journey.

- Product rule status may only become green after a successful server response from `/europakozijn/api/rules` with `valid: true`.
- A failed, unavailable, or malformed product-rule response must never be presented as green.
- PDOK/BAG confirmation proves address identity only; it does not prove wind load, glazing, safety glass, or ventilation performance.
- Wind load remains `Technische beoordeling nodig` until a verified calculation engine is connected.
- Glass and safety remain `Technische beoordeling nodig` until verified rules determine the required build-up.
- Ventilation input being complete is not equivalent to a calculated ventilation requirement.
- Reliable price remains blocked while those technical end checks are unresolved.
- Existing schema-v4 geometry and `/europakozijn/api/rules` remain the product-rule source of truth.
