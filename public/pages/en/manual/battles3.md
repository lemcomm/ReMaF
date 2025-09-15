### This manual page is detailing a highly experimental and incomplete feature that is in the process of being implemented. Everything on this page, while as detailed as it may be, is subject to change. ###

This manual page specifically details the "Mastery" Might & Fealty combat system. [Click here to view the "Legacy" combat system.](battles3)

Of particular note, this system is not currently in use for sieges, assaults, or sorties. It is, at this time, entirely opt-in for noble vs noble battles.

When [diplomacy] or [messages] cannot solve a dispute any longer, swords will. Battle is armed conflict between military forces, a single encounter on the battlefield.


Battle Preparations
-------------------
In the medieval world simulated by Might & Fealty, many concepts of modern warfare are non-existent. There is no trench warfare, no artillery and no air superiority. War is not fought by mobile infantry and Blitzkrieg maneuvers. Instead, war is quite often orchestrated, battlefields agreed upon beforehand and strategies well-known and familiar.

That said, battles are initiated by someone preparing to attack one or more opponents. This forces both sides to begin battle preparations, deploy troops, maneuver for optimal positions - a process that can take hours, and sometimes days. Many ancient and medieval battles actually worked very similarly to this, though of course for gameplay purposes the process has been abstracted and structured.

When you engage an enemy, whether attacking them on the open field or assaulting a settlement, you are beginning a timed action, with the time required depending on many factors, the most important being the armies involved. The more troops are involved in a battle, the longer preparations will take. The exception to this is for very one-sided battles. If one side outnumbers the other more than 10:1, additional troops will not make battle preparations take longer. Trying to stop a 1000 men army with 5 soldiers will not delay them very much.

While battle preparations are underway, the battle is visible to others in the area and everyone within action distance can join in, choosing freely which side they wish to support. First Ones joining the battle will also extend the preparation time, but with a decreasing impact (so you cannot delay a battle forever just by having more people join in continuously).

Once you have joined a battle, either by force or by initiating it, you cannot cancel it anymore. You can only avoid fighting a battle by evasive actions.

Battle Resolution
-----------------
All battles are resolved in two phases:

1. Ranged Phase
2. Melee Phase

Within these phases, all actions are resolved simultaneously.


### Ranged Phase ###
As the armies approach each other, archers and other ranged troops are first to open fire. They will get three volleys off before melee is joined, and thus cause casualties that have no chance to ever see melee. Losses suffered to archer fire will also impact morale and sanity of the fighting troops, and if the archer fire is especially deadly, might even cause some troops to flee combat before ever entering it properly.

### Melee Phase ###
As the armies clash, melee troops engage. Soldiers attack each other, and weapons, experience, armor and other equipment (such as shields) determine how many hits are scored and how deadly they are. While complex calculations go on in the background and combat is actually simulated at the level of individual soldiers, all you need to know are the results, which are summed up in the battle report.

In a field battle, the first round of the melee phase will also include a lancer charge, if there are any lancers on the field.

Soldiers will use whatever attack method they deem will do the most damage.

Again, casualties will affect morale, and there is a good chance that the battle will be decided by one side breaking and fleeing rather than one side being completely obliterated.

Morale & Sanity
------
As already mentioned, morale and sanity of soldiers is tracked during battle. All soldiers start at the same baseline mental state, regardless of equipment or race.

During battle, everything going on around the soldier affects his mental state. It improves when he hits or kills an enemy, it declines when he is attacked or hit, when his comrades fall around him or when he is outnumbered.

While you have no direct control over it, his mental state plays heavily into how he performs, making him retreat, go into a berserk rage, go delirious, feel heroic, or anything in between. This direct impacts his ability to attack and defend.

Battle Results
--------------
Maybe counter-intuitively, the game engine will not declare a winner for a battle. This is because battles do not happen in a vacuum. If the purpose of the battle was to delay the enemy invasion, then even a defeat can be a victory. If the enemy King died, it might be considered a victory even if you were driven off the battlefield. If you defeated an enemy with horrible losses even though you were numerically superior - is that really a victory?

No, the game leaves it to you to declare yourself victorious.


The game **does**, however, apply battle results for First Ones and individual soldiers on both sides. Your troops can come out of a battle routed, wounded and with lost or damaged equipment, forcing a resupply. They can, of course, also not come out of the battle alive at all.

The same is true for First Ones, except for equipment - First Ones are assumed to have spare equipment and capabilities to replace killed horses, etc. But First Ones can be victorious, defeated, wounded, killed or captured (see [prison]). They are considered victorious if their side is in control of the battlefield at the end of the battle, and defeated if all troops on their side were wiped out.

After-Battle Actions
--------------------
If anyone involved in battle had travel set, he will move a short distance (about 25% of daily travel) immediately after the battle. This represents retreat from the battlefield, orderly or not, and is also intended to prevent players from keeping their enemies stationary by engaging them in multiple small battles.

Anyone in a battle will also need to regroup afterward, during which time he cannot join or initiate new battles. The time needed to regroup depends on the number of soldiers under his command and will in most cases amount to somewhere between 30 minutes and 2 hours (real time).

Future Expansions
-----------------
Eventually, this system will include hunger modifications, persistent wounds (to include amputations), mid-battle skirmishes of different groups of soldiers, and be usable in sieges.