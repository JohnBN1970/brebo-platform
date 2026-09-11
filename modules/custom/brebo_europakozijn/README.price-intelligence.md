# Europakozijn Price Intelligence v1

This first version predicts observed supplier/configurator gross prices from calibrated examples. It deliberately keeps BREBO purchasing conditions separate from technical pricing.

## Current calibrated scope

- Aluplast Ideal 4000: simple fixed and tilt-turn frames around the controlled 1200 x 1200 reference.
- Aluplast Ideal 7000 NL Blockprofiel: simple fixed and tilt-turn frames using same-run controlled references at 1200 x 1200 and 980 x 1360.
- BREBO commercial discount: 49%, applied only after gross prediction.

## Explicitly not inferred yet

Mullions, transoms, doors, HST systems, no-glass configurations and unverified option effects are not silently guessed. They must be added from structured observations and blind validation.

## Model principle

Technical configuration -> gross prediction -> uncertainty band -> BREBO discount -> expected net purchase price.

## Website boundary

The public configurator provides a price indication, not a technically validated quotation. It may explain the known price drivers and uncertainty, but it must not expose BREBO purchasing conditions, internal unit prices, margins, detailed recipe data or a supplier-ready specification that turns the configurator into a free quotation-comparison tool.

The public journey is:

Building/address -> rooms -> frames -> configuration -> relevant technical signals -> price indication with basis -> request.

The request is then handed to BREBO Office. Office is authoritative for renewed validation and enrichment, final ventilation/glass/wind/product decisions, quantities, calculation, margin and the definitive quotation.

Public observations and customer-entered facts must remain distinguishable from detected, calculated and finally selected/approved data. A website indication must therefore never be promoted to technical truth merely because it was shown to the customer.
