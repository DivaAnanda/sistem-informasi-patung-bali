<h1 class="font-display text-3xl font-semibold sm:text-4xl">About the project</h1>
<p class="mt-5 text-lg leading-relaxed text-stone-700">
    Patung Bali is a digital record of the statues that stand at intersections and in public spaces across Bali.
    The website is the final project for the Digital Cultural Heritage course.
</p>

<div class="mt-12 space-y-12 leading-relaxed text-stone-800">
    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Why statues?</h2>
        <p class="mt-3">
            Bali's intersection statues usually depict figures from the Ramayana, the Mahabharata, history, and local
            culture. For residents they are landmarks for giving directions. For visitors they are a way into the stories
            that are still alive in Bali. Yet what is known about each figure, when it was built, and who sculpted it is
            scattered across news articles, journals, and government documents. We bring it together in one place.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Landmark statues and their themes</h2>
        <p class="mt-3">
            We treat every statue on this website as a <strong>landmark statue</strong>: a marker of place in public space.
            The statues are then told apart by the <strong>theme</strong> they depict. A statue can have more than one theme.
        </p>
        <dl class="mt-4 space-y-3">
            @foreach ($themes as $theme)
                <div>
                    <dt class="font-semibold">{{ $theme->getTranslation('name', 'en') }}</dt>
                    <dd class="text-stone-700">{{ $theme->getTranslation('description', 'en') }}</dd>
                </div>
            @endforeach
        </dl>
        <p class="mt-4 text-sm text-stone-600">
            The themes were drawn up with the cultural informant <strong>Dewa Gede Purwita, S.Pd., M.Sn.</strong>, a lecturer
            at Institut Desain dan Bisnis Bali (IDB Bali), in an interview on 20 November 2026.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Statues and the guardians of the crossroads</h2>
        <p class="mt-3">
            According to the palm-leaf text Tutur Gong Besi, every junction has a guardian of its own:
            <em>Sang Hyang Catur Bhuwana</em> at a crossroads and <em>Sang Hyang Sapuh Jagat</em> at a three-way junction.
            The offerings residents place at a junction are for that guardian, not for the statue. So a junction statue,
            even one consecrated in a pasupati ceremony, is not the main object of worship but a marker of place.
        </p>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">How the data was collected</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 marker:text-stone-400">
            <li>Reviewing secondary sources: news, academic journals, and official documents such as the 2021–2026 Strategic Plan of the Badung Environment and Forestry Agency (DLHK).</li>
            <li>Pinning each statue's coordinates and cross-checking them against map imagery.</li>
            <li>Classifying the intersection from OpenStreetMap road data, matched against written sources.</li>
            <li>Field surveys to photograph each statue together with the surrounding road.</li>
            <li>Classifying each statue's themes together with the cultural informant.</li>
        </ol>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Intersection types</h2>
        <dl class="mt-3 space-y-3">
            <div><dt class="font-semibold">Crossroads</dt><dd class="text-stone-700">A point where four roads meet.</dd></div>
            <div><dt class="font-semibold">T-junction</dt><dd class="text-stone-700">A point where three roads meet.</dd></div>
            <div><dt class="font-semibold">Roundabout</dt><dd class="text-stone-700">A circular intersection with the statue at its centre. The number of arms is listed on each statue's page.</dd></div>
            <div><dt class="font-semibold">Multi-way junction</dt><dd class="text-stone-700">Five or more roads meeting at one point.</dd></div>
            <div><dt class="font-semibold">Roadside</dt><dd class="text-stone-700">A statue beside the road rather than at a junction.</dd></div>
            <div><dt class="font-semibold">Inside a site</dt><dd class="text-stone-700">A statue inside a tourist area, park, or other compound.</dd></div>
        </dl>
    </section>

    <section>
        <h2 class="font-display text-2xl font-semibold text-ink">Sources and attribution</h2>
        <p class="mt-3">
            Maps use data © OpenStreetMap contributors under the ODbL licence. Every statue page lists its sources. The
            themes are based on an interview with the informant Dewa Gede Purwita, S.Pd., M.Sn. The dataset keeps growing
            as the team collects more statues.
        </p>
    </section>
</div>
